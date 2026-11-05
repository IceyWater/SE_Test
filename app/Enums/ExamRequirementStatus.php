<?php

namespace App\Enums;

enum ExamRequirementStatus: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case Lacking = 'lacking';
    case Waived = 'waived';
}
