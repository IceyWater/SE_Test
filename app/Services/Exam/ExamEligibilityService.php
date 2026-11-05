<?php

namespace App\Services\Exam;

use App\Models\ExamSetting;
use App\Models\StudentAcademicSnapshot;
use App\Models\User;

class ExamEligibilityService
{
    private const REQUIREMENTS = [
        'grades' => 'Complete passing grades',
        'clearance' => 'Clearance except Cashier',
        'tuition' => 'No outstanding balance',
        'documents' => 'Required documents uploaded',
        'program' => 'Program and curriculum verified',
    ];

    public function for(User $user): array
    {
        if ($this->demoEligibilityEnabled($user)) {
            return [
                'eligible' => true,
                'source' => 'Demo data',
                'demo' => true,
                'stale' => false,
                'as_of' => now()->toIso8601String(),
                'exam_open' => true,
                'requirements' => collect(self::REQUIREMENTS)
                    ->map(fn ($name, $key) => ['key' => $key, 'name' => $name, 'completed' => true])
                    ->values(),
            ];
        }

        $snapshot = StudentAcademicSnapshot::where('user_id', $user->id)
            ->whereNotNull('semester_id')
            ->latest('fetched_at')
            ->first();

        if (! $snapshot) {
            return $this->unknown('No verified academic snapshot is available.');
        }

        $stale = $snapshot->stale
            || ! $snapshot->fetched_at
            || $snapshot->fetched_at->lt(now()->subHours((int) config('exam.snapshot_max_age_hours', 6)));
        $gradesComplete = $snapshot->grades_summary['complete'] ?? null;
        $clearanceComplete = $snapshot->clearance_summary['complete'] ?? null;
        $programComplete = $snapshot->grades_summary['program_verified'] ?? null;
        $documentsComplete = $snapshot->clearance_summary['documents_complete'] ?? null;
        $tuitionComplete = (float) $snapshot->outstanding_balance <= 0;
        $examSetting = ExamSetting::where('school_year', $snapshot->school_year)->first();
        $examOpen = $examSetting
            ? (! $examSetting->opens_at || $examSetting->opens_at->lte(now()))
                && (! $examSetting->closes_at || $examSetting->closes_at->gte(now()))
            : null;

        $requirements = [
            ['key' => 'grades', 'name' => self::REQUIREMENTS['grades'], 'completed' => $gradesComplete],
            ['key' => 'clearance', 'name' => self::REQUIREMENTS['clearance'], 'completed' => $clearanceComplete],
            ['key' => 'tuition', 'name' => self::REQUIREMENTS['tuition'], 'completed' => $tuitionComplete],
            ['key' => 'documents', 'name' => self::REQUIREMENTS['documents'], 'completed' => $documentsComplete],
            ['key' => 'program', 'name' => self::REQUIREMENTS['program'], 'completed' => $programComplete],
            ['key' => 'exam_window', 'name' => 'Exam application window is open', 'completed' => $examOpen],
        ];
        $verified = collect($requirements)->every(fn ($requirement) => $requirement['completed'] === true);

        return [
            'eligible' => ! $stale && $verified,
            'source' => $snapshot->source,
            'demo' => false,
            'stale' => $stale,
            'as_of' => $snapshot->fetched_at?->toIso8601String(),
            'exam_open' => $examOpen,
            'requirements' => $requirements,
            'error' => $stale ? 'Academic snapshot is stale; request a refresh before applying.' : null,
        ];
    }

    private function unknown(string $message): array
    {
        return [
            'eligible' => false,
            'source' => null,
            'demo' => false,
            'stale' => false,
            'as_of' => null,
            'exam_open' => null,
            'requirements' => collect(self::REQUIREMENTS)
                ->map(fn ($name, $key) => ['key' => $key, 'name' => $name, 'completed' => null])
                ->push(['key' => 'exam_window', 'name' => 'Exam application window is open', 'completed' => null])
                ->values(),
            'error' => $message,
        ];
    }

    private function demoEligibilityEnabled(User $user): bool
    {
        return app()->environment('local')
            && (bool) config('exam.demo_eligibility')
            && (bool) $user->is_demo_account;
    }
}
