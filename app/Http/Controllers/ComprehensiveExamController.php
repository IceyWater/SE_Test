<?php

// app/Http/Controllers/ComprehensiveExamController.php

namespace App\Http\Controllers;

use App\Models\ExamApplication;
use App\Services\ComprehensiveEligibilityService;
use App\Services\Exam\ExamWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ComprehensiveExamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'Student', 403);
        $studentId = $user->student_id ?: $user->school_id ?: $user->id;

        $application = ExamApplication::with(['subjects.schedule.room', 'subjects.schedule.proctors.user', 'events.actor', 'requirementChecks'])
            ->whereIn('student_id', array_values(array_unique(array_filter([$studentId, $user->school_id, $user->id]))))
            ->latest('created_at')
            ->first();

        $response = null;
        if ($application) {
            $response = [
                'application_id' => $application->application_id,
                'application_no' => $application->application_no,
                'school_year' => $application->school_year,
                'workflow_status' => $application->workflow_status,
                'permit_status' => $application->permit_status,
                'final_approval_status' => $application->final_approval_status,
                'payment_status' => $application->payment_status,
                'aa_remarks' => $application->aa_remarks,
                'registrar_remarks' => $application->registrar_remarks,
                'submitted_at' => $application->submitted_at,
                'events' => $application->events->map(fn ($event) => [
                    'action' => $event->action,
                    'status' => $event->to_status,
                    'remarks' => $event->remarks,
                    'actor' => $event->actor?->full_name,
                    'created_at' => $event->created_at,
                ])->values(),
                'requirement_checks' => $application->requirementChecks->map(fn ($check) => [
                    'key' => $check->requirement_key,
                    'status' => $check->status,
                    'remarks' => $check->remarks,
                    'checked_at' => $check->checked_at,
                ])->values(),
                'contact_number' => $application->contact_number,
                'telephone_number' => $application->telephone_number,
                'office_address' => $application->office_address,
                'program' => $application->program,
                'created_at' => $application->created_at,
                // subjects show up now
                'subjects' => $application->subjects->map(fn ($s) => [
                    'subject' => $s->subject_name,
                    'date' => $s->schedule?->starts_at?->toDateString() ?? $s->exam_date,
                    'startTime' => $s->schedule?->starts_at?->format('H:i') ?? $s->start_time,
                    'endTime' => $s->schedule?->ends_at?->format('H:i') ?? $s->end_time,
                    'room' => $s->schedule?->room?->name,
                    'proctors' => $s->schedule?->proctors->map(fn ($proctor) => [
                        'name' => $proctor->user->full_name,
                        'status' => $proctor->status,
                    ])->values() ?? [],
                ]),
                // personal info from users table
                'first_name' => $user->first_name ?? '',
                'middle_name' => $user->middle_name ?? null,
                'last_name' => $user->last_name ?? '',
                'email' => $user->email ?? '',
                'student_id' => $studentId,
            ];
        }

        return Inertia::render('student/submissions/comprehensive-exam/Index', [
            'application' => $response,
            'eligibility' => app(ComprehensiveEligibilityService::class)->check($request),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->role === 'Student', 403);
        $validated = $this->validatedApplicationData($request);
        $user = $request->user();
        if ($validated['program'] !== $user->program) {
            throw ValidationException::withMessages(['program' => 'The application program must match your account.']);
        }

        $studentId = $user->student_id ?: $user->school_id ?: $user->id;
        if (! $studentId) {
            return back()->withErrors(['student_id' => 'Your account has no student ID associated.']);
        }

        if (ExamApplication::where('student_id', $studentId)->where('school_year', $validated['schoolYear'])->exists()) {
            return to_route('comprehensive-exam.index')->with('info', 'You already have an application for this school year.');
        }
        $eligibility = app(ComprehensiveEligibilityService::class)->check($request);
        if (($eligibility['eligible'] ?? false) !== true) {
            throw ValidationException::withMessages(['eligibility' => 'Eligibility has not been verified. Check your grades, documents, tuition clearance, and school portal session before applying.']);
        }

        return DB::transaction(function () use ($studentId, $validated, $user) {
            $application = ExamApplication::firstOrCreate(
                ['student_id' => $studentId, 'school_year' => $validated['schoolYear']],
                [
                    'permit_status' => 'pending',
                    'final_approval_status' => 'pending',
                    'contact_number' => $validated['mobileNo'],
                    'telephone_number' => $validated['telephoneNo'] ?? null,
                    'office_address' => $validated['officeAddress'] ?? null,
                    'program' => $user->program,
                ]
            );

            if (! $application->wasRecentlyCreated) {
                return to_route('comprehensive-exam.index')
                    ->with('info', 'You already have an application for this school year.');
            }

            foreach ($validated['subjects'] as $subject) {
                $application->subjects()->create([
                    'subject_name' => $subject['subject'],
                    'exam_date' => $subject['date'] ?? null,
                    'start_time' => $subject['startTime'] ?? null,
                    'end_time' => $subject['endTime'] ?? null,
                ]);
            }

            app(ExamWorkflowService::class)->recordSubmission($application, $user);

            return to_route('comprehensive-exam.index')->with('success', 'Application submitted.');
        });
    }

    public function resubmit(Request $request, ExamApplication $application, ExamWorkflowService $workflow)
    {
        abort_unless($request->user()?->role === 'Student', 403);
        $request->user()->can('view', $application) || abort(403);
        $validated = $this->validatedApplicationData($request);
        if ($validated['program'] !== $request->user()->program) {
            throw ValidationException::withMessages(['program' => 'The application program must match your account.']);
        }

        $workflow->studentResubmit($application, $request->user(), $validated);

        return to_route('comprehensive-exam.index')->with('success', 'Application corrections were resubmitted for review.');
    }

    private function validatedApplicationData(Request $request): array
    {
        return $request->validate([
            'schoolYear' => ['required', 'string', 'max:20'],
            'program' => ['required', 'string', 'max:255'],
            'officeAddress' => ['nullable', 'string', 'max:255'],
            'mobileNo' => ['required', 'string', 'max:50'],
            'telephoneNo' => ['nullable', 'string', 'max:50'],
            'subjects' => ['required', 'array', 'min:1'],
            'subjects.*.subject' => ['required', 'string', 'max:255'],
            'subjects.*.date' => ['nullable', 'date'],
            'subjects.*.startTime' => ['nullable', 'date_format:H:i'],
            'subjects.*.endTime' => ['nullable', 'date_format:H:i', 'after:subjects.*.startTime'],
        ]);
    }
}
