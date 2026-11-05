<?php

namespace App\Enums;

enum ExamApplicationStatus: string
{
    case Submitted = 'submitted';
    case AaLacking = 'aa_lacking';
    case AaEvaluated = 'aa_evaluated';
    case RegistrarApproved = 'registrar_approved';
    case RegistrarRejected = 'registrar_rejected';
    case PaymentPending = 'payment_pending';
    case PaymentRejected = 'payment_rejected';
    case PaymentVerified = 'payment_verified';
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
