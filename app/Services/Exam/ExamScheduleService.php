<?php

namespace App\Services\Exam;

use App\Enums\ExamApplicationStatus;
use App\Models\ExamApplication;
use App\Models\ExamApplicationSubject;
use App\Models\ExamProctor;
use App\Models\ExamRoom;
use App\Models\ExamSchedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ExamScheduleService
{
    public function assign(
        ExamApplication $application,
        ExamApplicationSubject $subject,
        User $actor,
        array $data
    ): ExamSchedule {
        return DB::transaction(function () use ($application, $subject, $actor, $data) {
            $lockedApplication = ExamApplication::whereKey($application->application_id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('schedule', $lockedApplication);
            abort_unless(
                in_array($lockedApplication->workflow_status, [
                    ExamApplicationStatus::PaymentVerified->value,
                    ExamApplicationStatus::Scheduled->value,
                ], true),
                409,
                'Only payment-verified applications can be scheduled.'
            );

            $lockedSubject = ExamApplicationSubject::whereKey($subject->getKey())
                ->where('application_id', $lockedApplication->application_id)
                ->firstOrFail();
            $room = ExamRoom::whereKey($data['room_id'])->where('is_active', true)->lockForUpdate()->firstOrFail();
            $proctorIds = collect($data['proctor_ids'])->map(fn ($id) => (int) $id)->unique()->sort()->values();
            $proctors = User::whereIn('id', $proctorIds)->where('role', 'Adviser')->orderBy('id')->lockForUpdate()->get();
            abort_unless($proctors->count() === $proctorIds->count(), 422, 'Select active Adviser accounts as proctors.');

            $startsAt = CarbonImmutable::parse($data['starts_at']);
            $endsAt = CarbonImmutable::parse($data['ends_at']);
            abort_unless($endsAt > $startsAt, 422, 'The schedule end time must be after its start time.');

            $schedule = ExamSchedule::where('exam_application_subject_id', $lockedSubject->id)
                ->lockForUpdate()
                ->first();

            abort_if(
                $schedule?->proctors()->where('status', 'accepted')->exists(),
                409,
                'This schedule has an accepted proctor; coordinate a new invitation before changing it.'
            );

            $roomOccupancy = ExamSchedule::where('room_id', $room->id)
                ->where('status', '!=', 'cancelled')
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->when($schedule, fn ($query) => $query->where('id', '<>', $schedule->id))
                ->count();
            if ($roomOccupancy >= $room->capacity) {
                throw new ConflictHttpException('The selected room has reached its capacity during this time.');
            }

            $studentConflict = ExamSchedule::where('exam_application_id', $lockedApplication->application_id)
                ->where('status', '!=', 'cancelled')
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->when($schedule, fn ($query) => $query->where('id', '<>', $schedule->id))
                ->exists();
            if ($studentConflict) {
                throw new ConflictHttpException('The student already has another exam scheduled during this time.');
            }

            $proctorConflict = DB::table('exam_proctors as p')
                ->join('exam_schedules as s', 's.id', '=', 'p.exam_schedule_id')
                ->whereIn('p.user_id', $proctorIds)
                ->whereIn('p.status', ['invited', 'accepted'])
                ->where('s.status', '!=', 'cancelled')
                ->where('s.starts_at', '<', $endsAt)
                ->where('s.ends_at', '>', $startsAt)
                ->when($schedule, fn ($query) => $query->where('s.id', '<>', $schedule->id))
                ->exists();
            if ($proctorConflict) {
                throw new ConflictHttpException('A selected proctor is already assigned during this time.');
            }

            $schedule = ExamSchedule::updateOrCreate(
                ['exam_application_subject_id' => $lockedSubject->id],
                [
                    'exam_application_id' => $lockedApplication->application_id,
                    'room_id' => $room->id,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'status' => 'published',
                    'created_by' => $actor->id,
                ]
            );
            $schedule->proctors()->delete();
            foreach ($proctors as $proctor) {
                $schedule->proctors()->create([
                    'user_id' => $proctor->id,
                    'role' => 'proctor',
                    'status' => 'invited',
                ]);
            }

            $subjectCount = $lockedApplication->subjects()->count();
            $scheduledCount = ExamSchedule::where('exam_application_id', $lockedApplication->application_id)
                ->where('status', 'published')
                ->count();
            if ($subjectCount > 0 && $scheduledCount === $subjectCount
                && $lockedApplication->workflow_status === ExamApplicationStatus::PaymentVerified->value) {
                app(ExamWorkflowService::class)->markScheduled($lockedApplication, $actor);
            }

            return $schedule->load(['room', 'proctors.user', 'subject']);
        });
    }

    public function respond(ExamProctor $invitation, User $actor, string $decision, ?string $reason = null): ExamProctor
    {
        abort_unless($actor->role === 'Adviser', 403);

        return DB::transaction(function () use ($invitation, $actor, $decision, $reason) {
            $locked = ExamProctor::whereKey($invitation->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((int) $locked->user_id === (int) $actor->id, 404);
            abort_unless($locked->status === 'invited', 409, 'This proctor invitation has already been answered.');
            User::whereKey($actor->id)->lockForUpdate()->firstOrFail();

            if ($decision === 'accepted') {
                $schedule = $locked->schedule()->firstOrFail();
                $conflict = DB::table('exam_proctors as p')
                    ->join('exam_schedules as s', 's.id', '=', 'p.exam_schedule_id')
                    ->where('p.user_id', $actor->id)
                    ->whereIn('p.status', ['invited', 'accepted'])
                    ->where('s.status', '!=', 'cancelled')
                    ->where('s.starts_at', '<', $schedule->ends_at)
                    ->where('s.ends_at', '>', $schedule->starts_at)
                    ->where('s.id', '<>', $schedule->id)
                    ->exists();
                if ($conflict) {
                    throw new ConflictHttpException('You are already assigned to another exam during this time.');
                }
            }

            $locked->status = $decision;
            $locked->responded_at = now();
            $locked->decline_reason = $decision === 'declined' ? $reason : null;
            $locked->save();

            return $locked;
        });
    }
}
