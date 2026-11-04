<?php

use App\Models\DefenseRequest;
use App\Services\AaReceiptQueue;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DefenseRequest::where('coordinator_status', 'Approved')->whereNotNull('proof_of_payment')
            ->chunkById(100, function ($requests) {
                foreach ($requests as $request) {
                    app(AaReceiptQueue::class)->ensure($request);
                }
            });
    }

    public function down(): void
    {
        // Keep receipt decisions and batches when rolling back this data repair.
    }
};
