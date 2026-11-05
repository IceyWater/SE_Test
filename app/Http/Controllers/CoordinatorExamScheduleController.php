<?php

namespace App\Http\Controllers;

use App\Mail\ExamWorkflowNotice;
use App\Models\ExamApplication;
use App\Models\ExamApplicationSubject;
use App\Models\ExamProctor;
use App\Models\ExamRoom;
use App\Models\Notification;
use App\Models\User;
use App\Services\Exam\ExamScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class CoordinatorExamScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'Coordinator', 403);
        $programs = $user->allowedProgramNames();

        $applications = ExamApplication::with(['subjects.schedule.room', 'subjects.schedule.proctors.user'])
            ->whereIn('program', $programs)
            ->whereIn('workflow_status', ['payment_verified', 'scheduled'])
            ->latest('application_id')
            ->limit(200)
            ->get()
            ->map(fn (ExamApplication $application) => [
                'id' => $application->application_id,
                'application_no' => $application->application_no,
                'student_id' => $application->student_id,
                'program' => $application->program,
                'school_year' => $application->school_year,
                'status' => $application->workflow_status,
                'subjects' => $application->subjects->map(fn (ExamApplicationSubject $subject) => [
                    'id' => $subject->id,
                    'name' => $subject->subject_name,
                    'schedule' => $subject->schedule ? [
                        'id' => $subject->schedule->id,
                        'room_id' => $subject->schedule->room_id,
                        'starts_at' => $subject->schedule->starts_at?->format('Y-m-d\TH:i'),
                        'ends_at' => $subject->schedule->ends_at?->format('Y-m-d\TH:i'),
                        'room' => $subject->schedule->room?->name,
                        'proctors' => $subject->schedule->proctors->map(fn (ExamProctor $proctor) => [
                            'id' => $proctor->id,
                            'user_id' => $proctor->user_id,
                            'name' => $proctor->user->full_name,
                            'status' => $proctor->status,
                        ])->values(),
                    ] : null,
                ])->values(),
            ])->values();

        return Inertia::render('coordinator/exam-schedules/Index', [
            'applications' => $applications,
            'rooms' => ExamRoom::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'capacity']),
            'proctors' => User::where('role', 'Adviser')->orderBy('last_name')->get()
                ->map(fn (User $adviser) => ['id' => $adviser->id, 'name' => $adviser->full_name])
                ->values(),
        ]);
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'Coordinator', 403);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:exam_rooms,code'],
            'name' => ['required', 'string', 'max:120'],
            'building' => ['nullable', 'string', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
        ]);
        ExamRoom::create($data + ['is_active' => true]);

        return back()->with('success', 'Exam room added.');
    }

    public function assign(
        Request $request,
        ExamApplication $application,
        ExamApplicationSubject $subject,
        ExamScheduleService $schedules
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user->role === 'Coordinator', 403);
        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:exam_rooms,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'proctor_ids' => ['required', 'array', 'min:1', 'max:2'],
            'proctor_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);
        $schedules->assign($application, $subject, $user, $data);

        $schedule = $application->schedules()->where('exam_application_subject_id', $subject->id)->firstOrFail();
        foreach ($schedule->proctors as $invitation) {
            $proctor = $invitation->user;
            $title = 'Comprehensive exam proctor invitation';
            $message = 'You have been invited to proctor a comprehensive exam. Review the schedule and respond.';
            $link = url('/exam/proctor-invitations');
            $notification = Notification::firstOrCreate(
                ['event_key' => "exam-proctor:{$invitation->id}"],
                [
                    'user_id' => $proctor->id,
                    'type' => 'exam_workflow',
                    'title' => $title,
                    'message' => $message,
                    'link' => $link,
                    'read' => false,
                    'exam_application_id' => $application->application_id,
                ]
            );
            if ($notification->wasRecentlyCreated && filled($proctor->email)) {
                Mail::to($proctor->email)->queue(new ExamWorkflowNotice($title, $message, $link));
            }
        }

        return back()->with('success', 'Exam schedule and proctor invitations saved.');
    }

    public function invitations(Request $request)
    {
        $user = $request->user();
        abort_unless($user->role === 'Adviser', 403);

        return Inertia::render('adviser/exam-proctor-invitations/Index', [
            'invitations' => ExamProctor::with(['schedule.application', 'schedule.subject', 'schedule.room'])
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->map(fn (ExamProctor $proctor) => [
                    'id' => $proctor->id,
                    'status' => $proctor->status,
                    'decline_reason' => $proctor->decline_reason,
                    'application_no' => $proctor->schedule->application->application_no,
                    'subject' => $proctor->schedule->subject->subject_name,
                    'room' => $proctor->schedule->room?->name,
                    'starts_at' => $proctor->schedule->starts_at?->toIso8601String(),
                    'ends_at' => $proctor->schedule->ends_at?->toIso8601String(),
                ]),
        ]);
    }

    public function respond(Request $request, ExamProctor $invitation, ExamScheduleService $schedules): RedirectResponse
    {
        abort_unless($request->user()->role === 'Adviser', 403);
        $data = $request->validate([
            'decision' => ['required', 'in:accepted,declined'],
            'reason' => ['required_if:decision,declined', 'nullable', 'string', 'min:5', 'max:500'],
        ]);
        $schedules->respond($invitation, $request->user(), $data['decision'], $data['reason'] ?? null);

        return back()->with('success', 'Your proctor response has been recorded.');
    }
}
