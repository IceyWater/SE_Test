<?php

namespace App\Services\Exam;

use Illuminate\Support\Facades\Log;

class LogOnlyExamSmsSender implements ExamSmsSender
{
    public function send(string $recipient, string $message, string $context): void
    {
        $digits = preg_replace('/\D+/', '', $recipient);
        Log::info('Comprehensive exam SMS queued through log-only adapter.', [
            'recipient_suffix' => $digits ? substr($digits, -3) : null,
            'context' => $context,
            'message' => $message,
        ]);
    }
}
