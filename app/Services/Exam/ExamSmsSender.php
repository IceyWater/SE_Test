<?php

namespace App\Services\Exam;

interface ExamSmsSender
{
    public function send(string $recipient, string $message, string $context): void;
}
