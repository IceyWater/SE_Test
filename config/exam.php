<?php

return [
    'fee' => (float) env('COMPRE_EXAM_FEE', 0),
    'demo_eligibility' => (bool) env('EXAM_DEMO_ELIGIBILITY', false),
    'aa_undo_minutes' => (int) env('EXAM_AA_UNDO_MINUTES', 30),
    'snapshot_max_age_hours' => (int) env('EXAM_SNAPSHOT_MAX_AGE_HOURS', 6),
    'max_receipt_kb' => (int) env('EXAM_MAX_RECEIPT_KB', 5120),
    'sms_enabled' => (bool) env('EXAM_SMS_ENABLED', false),
];
