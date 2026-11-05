<?php

namespace App\Listeners;

use App\Events\ExamStatusChanged;
use App\Mail\ExamWorkflowNotice;
use App\Models\Notification;
use App\Models\ExamSetting;
use App\Models\User;
use App\Services\Exam\ExamSmsSender;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendExamWorkflowNotifications
{
    public function handle(ExamStatusChanged $event): void
    {
        $action = $event->to->value;
        $notice = $this->notice($action);
        if (! $notice) {
            return;
        }

        [$title, $message, $path, $roles] = $notice;
        $recipients = $this->recipients($event, $roles);
        $eventId = (int) ($event->context['event_id'] ?? 0);

        foreach ($recipients as $recipient) {
            $notification = Notification::firstOrCreate(
                ['event_key' => "exam:{$eventId}:user:{$recipient->id}"],
                [
                    'user_id' => $recipient->id,
                    'type' => 'exam_workflow',
                    'title' => $title,
                    'message' => $message,
                    'link' => url($path),
                    'read' => false,
                    'exam_application_id' => $event->application->application_id,
                ]
            );

            if ($notification->wasRecentlyCreated && filled($recipient->email)) {
                Mail::to($recipient->email)->queue(new ExamWorkflowNotice(
                    $title,
                    $message,
                    url($path)
                ));
            }
        }

        $setting = ExamSetting::where('school_year', $event->application->school_year)->first();
        $mobile = $event->application->contact_number;
        if (config('exam.sms_enabled') && $setting?->sms_enabled && filled($mobile)) {
            app(ExamSmsSender::class)->send(
                $mobile,
                $message,
                "application:{$event->application->application_id}:{$action}"
            );
        }
    }

    private function recipients(ExamStatusChanged $event, array $roles): Collection
    {
        $users = collect();
        if (in_array('Student', $roles, true)) {
            $application = $event->application;
            $users = $users->merge(User::query()
                ->where(function ($query) use ($application) {
                    $query->where('school_id', $application->student_id)
                        ->orWhere('id', $application->student_id);
                })
                ->get());
        }

        $roleNames = array_values(array_filter($roles, fn ($role) => $role !== 'Student' && $role !== 'Coordinator'));
        if ($roleNames) {
            $users = $users->merge(User::whereIn('role', $roleNames)->get());
        }

        if (in_array('Coordinator', $roles, true)) {
            $coordinators = User::where('role', 'Coordinator')->get()
                ->filter(fn (User $user) => in_array($event->application->program, $user->allowedProgramNames(), true));
            $users = $users->merge($coordinators);
        }

        return $users->unique('id')->values();
    }

    private function notice(string $status): ?array
    {
        return match ($status) {
            'submitted' => ['Comprehensive exam application submitted', 'A student application is ready for administrative review.', '/exam/aa/reviews', ['Administrative Assistant']],
            'aa_lacking' => ['Additional documents required', 'Your comprehensive exam application requires updates. Review the application notes.', '/comprehensive-exam', ['Student']],
            'aa_evaluated' => ['Application ready for Registrar review', 'An application has completed the AA requirements review.', '/exam/registrar/reviews', ['Registrar']],
            'registrar_approved' => ['Comprehensive exam application approved', 'Your application was approved. You may continue to the payment step.', '/payment', ['Student']],
            'registrar_rejected' => ['Comprehensive exam application needs attention', 'The Registrar returned your application. Review the application notes.', '/comprehensive-exam', ['Student']],
            'payment_pending' => ['Comprehensive exam payment submitted', 'A payment receipt is ready for verification.', '/coordinator/compre-payment', ['Administrative Assistant', 'Coordinator']],
            'payment_verified' => ['Comprehensive exam payment verified', 'Your payment has been verified.', '/comprehensive-exam', ['Student']],
            'payment_rejected' => ['Comprehensive exam payment needs attention', 'Your payment receipt was returned. Review the remarks and submit a new attempt.', '/payment', ['Student']],
            'scheduled' => ['Comprehensive exam schedule published', 'Your exam schedule is available in the comprehensive exam application timeline.', '/comprehensive-exam', ['Student']],
            default => null,
        };
    }
}
