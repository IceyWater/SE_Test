<?php

namespace App\Events;

use App\Enums\ExamApplicationStatus;
use App\Models\ExamApplication;
use App\Models\User;

class ExamStatusChanged
{
    public function __construct(
        public ExamApplication $application,
        public ?ExamApplicationStatus $from,
        public ExamApplicationStatus $to,
        public ?User $actor = null,
        public array $context = [],
    ) {}
}
