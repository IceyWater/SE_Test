<?php

namespace App\Services;

use App\Models\AaPaymentVerification;
use App\Models\DefenseRequest;

class AaReceiptQueue
{
    public function ensure(DefenseRequest $request): void
    {
        if ($request->coordinator_status !== 'Approved' || ! $request->proof_of_payment
            || app(HonorariumAccess::class)->isArchived($request)) {
            return;
        }
        AaPaymentVerification::firstOrCreate(['defense_request_id' => $request->id], ['status' => 'pending']);
    }
}
