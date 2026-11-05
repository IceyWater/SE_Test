<?php

namespace App\Http\Controllers\Exam;

use App\Models\ExamApplication;
use App\Models\User;
use App\Services\Exam\ExamWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class RegistrarExamApprovalController
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'Registrar', 403);

        $applications = ExamApplication::with(['requirementChecks', 'events.actor'])
            ->whereIn('workflow_status', ['aa_evaluated', 'registrar_approved', 'registrar_rejected'])
            ->latest('aa_reviewed_at')
            ->limit(200)
            ->get()
            ->map(fn (ExamApplication $application) => $this->present($application));

        return Inertia::render('registrar/comprehensive-exam/Index', ['applications' => $applications]);
    }

    public function approve(Request $request, ExamApplication $application, ExamWorkflowService $workflow)
    {
        $this->authorizeApplication($request, $application);
        $workflow->registrarApprove($application, $request->user());

        return back()->with('success', 'Application approved. The student is cleared to pay the configured exam fee.');
    }

    public function reject(Request $request, ExamApplication $application, ExamWorkflowService $workflow)
    {
        $this->authorizeApplication($request, $application);
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);
        $workflow->registrarReject($application, $request->user(), $data['reason']);

        return back()->with('success', 'Application rejected with a reason recorded.');
    }

    private function authorizeApplication(Request $request, ExamApplication $application): void
    {
        $request->user()->can('approveRegistrar', $application) || abort(403);
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
            'program' => $application->program,
            'school_year' => $application->school_year,
            'status' => $application->workflow_status,
            'aa_remarks' => $application->aa_remarks,
            'checks' => $application->requirementChecks->map(fn ($check) => [
                'key' => $check->requirement_key,
                'status' => $check->status,
                'remarks' => $check->remarks,
            ])->values(),
        ];
    }
}
