<?php

return [
    'hosted_enabled' => filter_var(env('HOSTED_DEMO_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'finance_accounts' => ['finance@uic.edu.ph' => ['role' => 'Finance']],
    // Local-only profiles for exercising different research tracks; not provisioned on hosted deployments.
    'local_student_accounts' => [
        'doctorate@uic.edu.ph' => ['role' => 'Student', 'first_name' => 'Demo', 'last_name' => 'Doctorate Student', 'school_id' => 'DEMO-DBM', 'program' => 'Doctor in Business Management'],
        'masterit@uic.edu.ph' => ['role' => 'Student', 'first_name' => 'Demo', 'last_name' => 'IT Student', 'school_id' => 'DEMO-MIT', 'program' => 'Master in Information Technology'],
        'masterpharmacy@uic.edu.ph' => ['role' => 'Student', 'first_name' => 'Demo', 'last_name' => 'Pharmacy Student', 'school_id' => 'DEMO-MSPH', 'program' => 'Master of Science in Pharmacy'],
        'mastermba@uic.edu.ph' => ['role' => 'Student', 'first_name' => 'Demo', 'last_name' => 'MBA Student', 'school_id' => 'DEMO-MBA-NT', 'program' => 'Master in Business Administration (Non-Thesis)'],
    ],
    'accounts' => [
        'student@uic.edu.ph' => ['role' => 'Student', 'first_name' => 'Demo', 'last_name' => 'Student', 'school_id' => 'DEMO-STUDENT'],
        'adviser@uic.edu.ph' => ['role' => 'Adviser', 'first_name' => 'Demo', 'last_name' => 'Adviser', 'school_id' => 'DEMO-ADVISER'],
        'acadcoor@uic.edu.ph' => ['role' => 'Coordinator', 'first_name' => 'Demo', 'last_name' => 'Coordinator', 'school_id' => 'DEMO-COORDINATOR'],
        'aa@uic.edu.ph' => ['role' => 'Administrative Assistant', 'first_name' => 'Demo', 'last_name' => 'Assistant', 'school_id' => 'DEMO-AA'],
        'dean@uic.edu.ph' => ['role' => 'Dean', 'first_name' => 'Demo', 'last_name' => 'Dean', 'school_id' => 'DEMO-DEAN'],
        'student2@uic.edu.ph' => [
            'role' => 'Student',
            'first_name' => 'Test',
            'last_name' => 'Student Two',
            'school_id' => 'DEMO-STUDENT-2',
        ],
    ],
];
