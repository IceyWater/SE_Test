<?php

namespace App\Policies;

use App\Enums\ExamApplicationStatus;
use App\Models\ExamApplication;
use App\Models\User;

class ExamApplicationPolicy
{
    public function view(User $user, ExamApplication $application): bool
    {
        if ($user->role === 'Student') {
            return (string) $application->student_id === (string) ($user->student_id ?: $user->school_id ?: $user->id);
        }

        if (in_array($user->role, ['Administrative Assistant', 'Registrar', 'Dean', 'Finance'], true)) {
            return true;
        }

        return $this->coordinatorOwnsProgram($user, $application);
    }

    public function reviewAa(User $user, ExamApplication $application): bool
    {
        return $user->role === 'Administrative Assistant';
    }

    public function approveRegistrar(User $user, ExamApplication $application): bool
    {
        return $user->role === 'Registrar';
    }

    public function schedule(User $user, ExamApplication $application): bool
    {
        return $this->coordinatorOwnsProgram($user, $application)
            && in_array($application->workflow_status, [
                ExamApplicationStatus::PaymentVerified->value,
                ExamApplicationStatus::Scheduled->value,
            ], true);
    }

    public function verifyPayment(User $user, ExamApplication $application): bool
    {
        return $user->role === 'Administrative Assistant'
            || $this->coordinatorOwnsProgram($user, $application);
    }

    private function coordinatorOwnsProgram(User $user, ExamApplication $application): bool
    {
        return $user->isCoordinator()
            && in_array($application->program, $user->allowedProgramNames(), true);
    }
}
