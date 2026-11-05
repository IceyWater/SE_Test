<?php

namespace App\Http\Controllers\Exam;

use App\Enums\ExamRequirementStatus;
use App\Models\ExamApplication;
use App\Models\User;
use App\Services\Exam\ExamWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class AaExamReviewController
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'Administrative Assistant', 403);

        $applications = ExamApplication::with('requirementChecks')
            ->whereIn('workflow_status', ['submitted', 'aa_lacking'])
            ->latest('submitted_at')
            ->limit(200)
            ->get()
            ->map(fn (ExamApplication $application) => $this->present($application));

        return Inertia::render('aa/comprehensive-exam/Index', [
            'applications' => $applications,
            'requirements' => [
                ['key' => 'grades', 'label' => 'Complete passing grades'],
                ['key' => 'clearance', 'label' => 'Clearance except Cashier'],
                ['key' => 'tuition', 'label' => 'No outstanding balance'],
                ['key' => 'documents', 'label' => 'Required documents'],
                ['key' => 'program', 'label' => 'Program and curriculum'],
            ],
        ]);
    }

    public function checkRequirement(Request $request, ExamApplication $application, ExamWorkflowService $workflow)
    {
        $this->authorizeApplication($request, $application);
        $data = $request->validate([
            'requirement_key' => ['required', 'string', 'max:60'],
            'status' => ['required', 'in:pending,passed,lacking,waived'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);
        if ($data['status'] === ExamRequirementStatus::Lacking->value && trim((string) ($data['remarks'] ?? '')) === '') {
            return back()->withErrors(['remarks' => 'A reason is required when a requirement is marked Lacking.']);
        }

        $workflow->checkRequirement(
            $application,
            $request->user(),
            $data['requirement_key'],
            ExamRequirementStatus::from($data['status']),
            $data['remarks'] ?? null
        );

        return back()->with('success', 'Requirement review saved.');
    }

    public function markLacking(Request $request, ExamApplication $application, ExamWorkflowService $workflow)
    {
        $this->authorizeApplication($request, $application);
        $data = $request->validate(['remarks' => ['required', 'string', 'min:10', 'max:2000']]);
        $workflow->aaMarkLacking($application, $request->user(), $data['remarks']);

        return back()->with('success', 'Application marked Lacking; the student can now correct and resubmit.');
    }

    public function markEvaluated(Request $request, ExamApplication $application, ExamWorkflowService $workflow)
    {
        $this->authorizeApplication($request, $application);
        $workflow->aaMarkEvaluated($application, $request->user());

        return back()->with('success', 'Application evaluated and forwarded to the Registrar.');
    }

    public function undo(Request $request, ExamApplication $application, ExamWorkflowService $workflow)
    {
        $this->authorizeApplication($request, $application);
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);
        $workflow->aaUndo($application, $request->user(), $data['reason']);

        return back()->with('success', 'Evaluation undone. The application returned to AA review.');
    }

    private function authorizeApplication(Request $request, ExamApplication $application): void
    {
        $request->user()->can('reviewAa', $application) || abort(403);
    }

    private function present(ExamApplication $application): array
    {
        $student = User::query()->where(function ($query) use ($application) {
            $query->where('school_id', (string) $application->student_id);
            if (Schema::hasColumn('users', 'student_id')) {
                $query->orWhere('student_id', (string) $application->student_id);
            }
        })->first();
        if (! $student && ctype_digit((string) $application->student_id)) {
            $student = User::find((int) $application->student_id);
        }

        return [
            'id' => $application->application_id,
            'application_no' => $application->application_no,
            'student_name' => $student?->full_name ?? 'Student account unavailable',
            'student_email' => $student?->email,
            'program' => $application->program,
            'school_year' => $application->school_year,
            'status' => $application->workflow_status,
            'submitted_at' => $application->submitted_at?->toIso8601String(),
            'aa_remarks' => $application->aa_remarks,
            'checks' => $application->requirementChecks->map(fn ($check) => [
                'key' => $check->requirement_key,
                'status' => $check->status,
                'remarks' => $check->remarks,
            ])->values(),
        ];
    }
}
