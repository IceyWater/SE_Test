<?php

namespace App\Services\Exam;

use App\Enums\ExamApplicationStatus;
use App\Enums\ExamRequirementStatus;
use App\Events\ExamStatusChanged;
use App\Models\ExamApplication;
use App\Models\ExamApplicationEvent;
use App\Models\ExamRequirementCheck;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ExamWorkflowService
{
    private const REQUIREMENTS = ['grades', 'clearance', 'tuition', 'documents', 'program'];

    public function recordSubmission(ExamApplication $application, User $student): ExamApplication
    {
        return DB::transaction(function () use ($application, $student) {
            $locked = ExamApplication::whereKey($application->getKey())->lockForUpdate()->firstOrFail();
            abort_unless(
                (string) $locked->student_id === (string) ($student->student_id ?: $student->school_id ?: $student->id),
                403
            );

            $locked->workflow_status = ExamApplicationStatus::Submitted->value;
            $locked->submitted_at = now();
            $locked->application_no ??= sprintf('CE-%s-%06d', now()->format('Y'), $locked->application_id);
            $locked->version = max(1, (int) $locked->version);
            $locked->updated_at = now();
            $locked->save();

            foreach (self::REQUIREMENTS as $key) {
                ExamRequirementCheck::firstOrCreate([
                    'exam_application_id' => $locked->application_id,
                    'requirement_key' => $key,
                ], ['status' => ExamRequirementStatus::Pending->value]);
            }

            $eventId = $this->writeEvent($locked, $student, 'submitted', null, ExamApplicationStatus::Submitted, null);
            $this->dispatchAfterCommit($locked, null, ExamApplicationStatus::Submitted, $student, $eventId);

            return $locked;
        });
    }

    public function checkRequirement(
        ExamApplication $application,
        User $actor,
        string $key,
        ExamRequirementStatus $status,
        ?string $remarks
    ): ExamRequirementCheck {
        return DB::transaction(function () use ($application, $actor, $key, $status, $remarks) {
            $locked = ExamApplication::whereKey($application->getKey())->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('reviewAa', $locked);
            $this->expectStatus($locked, [ExamApplicationStatus::Submitted]);
            abort_unless(in_array($key, self::REQUIREMENTS, true), 422, 'Unknown comprehensive-exam requirement.');

            $check = ExamRequirementCheck::updateOrCreate(
                ['exam_application_id' => $locked->application_id, 'requirement_key' => $key],
                [
                    'status' => $status->value,
                    'remarks' => $remarks,
                    'checked_by' => $actor->id,
                    'checked_at' => now(),
                ]
            );
            $locked->version = (int) $locked->version + 1;
            $locked->updated_at = now();
            $locked->save();
            $this->writeEvent($locked, $actor, 'requirement_checked', $locked->workflow_status, ExamApplicationStatus::Submitted, $remarks, [
                'requirement_key' => $key,
                'requirement_status' => $status->value,
            ]);

            return $check;
        });
    }

    public function aaMarkLacking(ExamApplication $application, User $actor, string $remarks): ExamApplication
    {
        return $this->transition(
            $application,
            $actor,
            'reviewAa',
            [ExamApplicationStatus::Submitted],
            ExamApplicationStatus::AaLacking,
            'aa_lacking',
            function (ExamApplication $locked) use ($actor, $remarks) {
                abort_unless(
                    $locked->requirementChecks()->where('status', ExamRequirementStatus::Lacking->value)->exists(),
                    409,
                    'Mark at least one requirement as Lacking before submitting this decision.'
                );
                $locked->aa_remarks = $remarks;
                $locked->aa_reviewed_at = now();
                $locked->aa_reviewed_by = $actor->id;
            },
            $remarks
        );
    }

    public function aaMarkEvaluated(ExamApplication $application, User $actor): ExamApplication
    {
        return $this->transition(
            $application,
            $actor,
            'reviewAa',
            [ExamApplicationStatus::Submitted],
            ExamApplicationStatus::AaEvaluated,
            'aa_evaluated',
            function (ExamApplication $locked) use ($actor) {
                $checks = $locked->requirementChecks()->get()->keyBy('requirement_key');
                $allPassed = collect(self::REQUIREMENTS)->every(fn ($key) => in_array(
                    $checks->get($key)?->status,
                    [ExamRequirementStatus::Passed->value, ExamRequirementStatus::Waived->value],
                    true
                ));
                abort_unless($allPassed, 409, 'Resolve every requirement before marking Evaluated.');
                $locked->aa_reviewed_at = now();
                $locked->aa_reviewed_by = $actor->id;
                $locked->aa_remarks = null;
            },
            null
        );
    }

    public function aaUndo(ExamApplication $application, User $actor, string $reason): ExamApplication
    {
        return $this->transition(
            $application,
            $actor,
            'reviewAa',
            [ExamApplicationStatus::AaEvaluated],
            ExamApplicationStatus::Submitted,
            'aa_undo',
            function (ExamApplication $locked) {
                abort_if($locked->registrar_reviewed_at !== null, 409, 'The Registrar has already acted.');
                abort_if(
                    $locked->aa_reviewed_at?->lt(now()->subMinutes((int) config('exam.aa_undo_minutes', 30))),
                    409,
                    'The undo window has expired.'
                );
                $locked->aa_reviewed_at = null;
                $locked->aa_reviewed_by = null;
            },
            $reason
        );
    }

    public function registrarApprove(ExamApplication $application, User $actor): ExamApplication
    {
        return $this->transition(
            $application,
            $actor,
            'approveRegistrar',
            [ExamApplicationStatus::AaEvaluated],
            ExamApplicationStatus::RegistrarApproved,
            'registrar_approved',
            function (ExamApplication $locked) use ($actor) {
                $locked->registrar_reviewed_at = now();
                $locked->registrar_reviewed_by = $actor->id;
                $locked->final_approval_status = 'approved';
                $locked->final_approval_DATE = now();
                $locked->permit_status = 'approved';
                $locked->permit_DATE = now();
                $locked->payment_status = 'not_paid';
            },
            null
        );
    }

    public function registrarReject(ExamApplication $application, User $actor, string $reason): ExamApplication
    {
        return $this->transition(
            $application,
            $actor,
            'approveRegistrar',
            [ExamApplicationStatus::AaEvaluated],
            ExamApplicationStatus::RegistrarRejected,
            'registrar_rejected',
            function (ExamApplication $locked) use ($actor, $reason) {
                $locked->registrar_reviewed_at = now();
                $locked->registrar_reviewed_by = $actor->id;
                $locked->registrar_remarks = $reason;
                $locked->final_approval_status = 'rejected';
                $locked->final_approval_DATE = now();
                $locked->permit_status = 'rejected';
                $locked->permit_reason = $reason;
            },
            $reason
        );
    }

    public function paymentSubmitted(ExamApplication $application, User $student): ExamApplication
    {
        return $this->transition(
            $application,
            $student,
            'view',
            [ExamApplicationStatus::RegistrarApproved, ExamApplicationStatus::PaymentRejected],
            ExamApplicationStatus::PaymentPending,
            'payment_submitted',
            function (ExamApplication $locked) use ($student) {
                abort_unless(
                    (string) $locked->student_id === (string) ($student->student_id ?: $student->school_id ?: $student->id),
                    403
                );
                $locked->payment_status = 'pending';
            },
            null
        );
    }

    public function paymentVerified(ExamApplication $application, User $actor): ExamApplication
    {
        return $this->transition(
            $application,
            $actor,
            'verifyPayment',
            [ExamApplicationStatus::PaymentPending],
            ExamApplicationStatus::PaymentVerified,
            'payment_verified',
            function (ExamApplication $locked) use ($actor) {
                $locked->payment_status = 'verified';
                $locked->approved_by = $actor->id;
            },
            null
        );
    }

    public function paymentRejected(ExamApplication $application, User $actor, string $reason): ExamApplication
    {
        return $this->transition(
            $application,
            $actor,
            'verifyPayment',
            [ExamApplicationStatus::PaymentPending],
            ExamApplicationStatus::PaymentRejected,
            'payment_rejected',
            function (ExamApplication $locked) use ($reason) {
                $locked->payment_status = 'rejected';
                $locked->permit_reason = $reason;
            },
            $reason
        );
    }

    public function studentResubmit(ExamApplication $application, User $student, array $data): ExamApplication
    {
        abort_unless($student->role === 'Student', 403);

        return $this->transition(
            $application,
            $student,
            'view',
            [ExamApplicationStatus::AaLacking, ExamApplicationStatus::RegistrarRejected],
            ExamApplicationStatus::Submitted,
            'student_resubmitted',
            function (ExamApplication $locked) use ($student, $data) {
                abort_unless(
                    (string) $locked->student_id === (string) ($student->student_id ?: $student->school_id ?: $student->id),
                    403
                );

                $locked->contact_number = $data['mobileNo'];
                $locked->telephone_number = $data['telephoneNo'] ?? null;
                $locked->office_address = $data['officeAddress'] ?? null;
                $locked->program = $student->program;
                $locked->submitted_at = now();
                $locked->aa_reviewed_at = null;
                $locked->aa_reviewed_by = null;
                $locked->aa_remarks = null;
                $locked->registrar_reviewed_at = null;
                $locked->registrar_reviewed_by = null;
                $locked->registrar_remarks = null;
                $locked->final_approval_status = 'pending';
                $locked->final_approval_DATE = null;
                $locked->approved_by = null;
                $locked->permit_status = 'pending';
                $locked->permit_DATE = null;
                $locked->permit_reason = null;
                $locked->payment_status = 'not_paid';

                $locked->requirementChecks()->update([
                    'status' => ExamRequirementStatus::Pending->value,
                    'remarks' => null,
                    'checked_by' => null,
                    'checked_at' => null,
                ]);
                $locked->subjects()->delete();
                foreach ($data['subjects'] as $subject) {
                    $locked->subjects()->create([
                        'subject_name' => $subject['subject'],
                        'exam_date' => $subject['date'] ?? null,
                        'start_time' => $subject['startTime'] ?? null,
                        'end_time' => $subject['endTime'] ?? null,
                    ]);
                }
            },
            null
        );
    }

    public function markScheduled(ExamApplication $application, User $actor): ExamApplication
    {
        return $this->transition(
            $application,
            $actor,
            'schedule',
            [ExamApplicationStatus::PaymentVerified],
            ExamApplicationStatus::Scheduled,
            'scheduled',
            function (ExamApplication $locked) {
                $locked->scheduled_at = now();
            },
            null
        );
    }

    private function transition(
        ExamApplication $application,
        User $actor,
        string $ability,
        array $expected,
        ExamApplicationStatus $target,
        string $action,
        callable $mutate,
        ?string $remarks
    ): ExamApplication {
        return DB::transaction(function () use ($application, $actor, $ability, $expected, $target, $action, $mutate, $remarks) {
            $locked = ExamApplication::whereKey($application->getKey())->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize($ability, $locked);
            $this->expectStatus($locked, $expected);
            $from = ExamApplicationStatus::from($locked->workflow_status);
            $mutate($locked);
            $locked->workflow_status = $target->value;
            $locked->version = (int) $locked->version + 1;
            $locked->updated_at = now();
            $locked->save();

            $eventId = $this->writeEvent($locked, $actor, $action, $from->value, $target, $remarks);
            $this->dispatchAfterCommit($locked, $from, $target, $actor, $eventId);

            return $locked;
        });
    }

    private function expectStatus(ExamApplication $application, array $expected): void
    {
        $allowed = array_map(fn (ExamApplicationStatus $status) => $status->value, $expected);
        if (! in_array($application->workflow_status, $allowed, true)) {
            throw new ConflictHttpException('This comprehensive-exam application has already changed. Refresh and try again.');
        }
    }

    private function writeEvent(
        ExamApplication $application,
        User $actor,
        string $action,
        ?string $from,
        ExamApplicationStatus $to,
        ?string $remarks,
        array $metadata = []
    ): int {
        $event = ExamApplicationEvent::create([
            'exam_application_id' => $application->application_id,
            'actor_id' => $actor->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to->value,
            'remarks' => $remarks,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);

        return (int) $event->getKey();
    }

    private function dispatchAfterCommit(
        ExamApplication $application,
        ?ExamApplicationStatus $from,
        ExamApplicationStatus $to,
        User $actor,
        int $eventId
    ): void {
        DB::afterCommit(fn () => event(new ExamStatusChanged($application, $from, $to, $actor, ['event_id' => $eventId])));
    }
}
