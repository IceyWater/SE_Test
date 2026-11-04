<?php

namespace App\Services;

use App\Models\ComprehensiveExamResult;
use App\Models\DefenseAcademicOutcome;
use App\Models\DefenseRequest;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class DefenseSubmissionEligibility
{
    public function examRecord(User $student): ?ComprehensiveExamResult
    {
        return Schema::hasTable('comprehensive_exam_results')
            ? ComprehensiveExamResult::where('student_id', $student->id)->where('program', (string) $student->program)->first()
            : null;
    }

    public function forStudent(User $student): array
    {
        $exam = $this->examRecord($student);
        $passed = $exam?->result === 'passed' && $exam->exam_date?->toDateString() <= now('Asia/Manila')->toDateString();
        $requests = DefenseRequest::where('submitted_by', $student->id)->where('program', (string) $student->program)->latest('id')->get();
        $outcomes = DefenseAcademicOutcome::whereIn('defense_request_id', $requests->modelKeys())->get()->keyBy('defense_request_id');
        $completed = [];
        foreach (['Proposal', 'Prefinal'] as $stage) {
            $latest = $requests->first(fn ($row) => strtolower($row->defense_type ?? '') === strtolower($stage));
            $outcome = $latest ? $outcomes->get($latest->id) : null;
            $completed[$stage] = $latest && $outcome && app(DefenseAcademicProgress::class)->eligible($latest, $outcome);
        }
        $stages = [];
        foreach (['Proposal', 'Prefinal', 'Final'] as $stage) {
            $reason = ! $passed ? 'A Coordinator must verify a passed comprehensive exam result for your current program before you can submit a defense request.' : null;
            if (! $reason && $stage !== 'Proposal' && ! $completed['Proposal']) {
                $reason = 'Your Proposal Defense must be recorded as Done / Passed before requesting '.$stage.' Defense.';
            }
            if (! $reason && $stage === 'Final' && ! $completed['Prefinal']) {
                $reason = 'Your Prefinal Defense must be recorded as Done / Passed before requesting Final Defense.';
            }
            $stages[$stage] = ['allowed' => $reason === null, 'reason' => $reason];
        }

        return ['comprehensive_passed' => $passed, 'stages' => $stages];
    }

    public function assertCanSubmit(User $student, string $stage): void
    {
        abort_unless($student->role === 'Student', 403);
        $eligibility = $this->forStudent($student);
        $reason = $eligibility['stages'][$stage]['reason'] ?? (isset($eligibility['stages'][$stage]) ? null : 'Choose a valid defense type.');
        if ($reason) {
            throw ValidationException::withMessages(['defense_type' => $reason, 'message' => $reason]);
        }
    }
}
