<?php

namespace App\Console\Commands;

use App\Models\PaymentSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SecureExamPaymentReceipts extends Command
{
    protected $signature = 'exam:secure-payment-receipts {--apply : Copy legacy public receipts to private storage and remove public copies}';

    protected $description = 'Review or migrate legacy comprehensive exam receipts from public to private storage';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $payments = PaymentSubmission::where('payment_type', 'exam')
            ->whereNotNull('receipt_image')
            ->orderBy('payment_id');
        $count = $payments->count();
        $this->info(($apply ? 'Migrating' : 'Would migrate').' up to '.$count.' comprehensive exam receipt(s).');

        if (! $apply) {
            $this->comment('Run with --apply only after reviewing the deployment backup and storage permissions.');

            return self::SUCCESS;
        }

        $payments->chunkById(100, function ($records) {
            foreach ($records as $payment) {
                $path = ltrim((string) $payment->receipt_image, '/');
                if ($path === '' || str_contains($path, '..')) {
                    throw new RuntimeException("Unsafe receipt path for payment {$payment->payment_id}.");
                }

                if (! Storage::disk('local')->exists($path)) {
                    if (! Storage::disk('public')->exists($path)) {
                        $this->warn("Missing public receipt for payment {$payment->payment_id}; left unchanged.");

                        continue;
                    }

                    $stream = Storage::disk('public')->readStream($path);
                    if (! is_resource($stream)) {
                        throw new RuntimeException("Could not read receipt for payment {$payment->payment_id}.");
                    }

                    try {
                        if (! Storage::disk('local')->writeStream($path, $stream)) {
                            throw new RuntimeException("Could not copy receipt for payment {$payment->payment_id}.");
                        }
                    } finally {
                        fclose($stream);
                    }
                }

                $privateHash = hash_file('sha256', Storage::disk('local')->path($path));
                if (! is_string($privateHash)) {
                    throw new RuntimeException("Could not verify the private receipt for payment {$payment->payment_id}.");
                }
                if ($payment->receipt_sha256 && ! hash_equals($payment->receipt_sha256, $privateHash)) {
                    throw new RuntimeException("Receipt checksum mismatch for payment {$payment->payment_id}; public source was retained.");
                }

                DB::transaction(function () use ($payment, $privateHash) {
                    $payment->forceFill(['receipt_sha256' => $privateHash])->save();
                });
                if (Storage::disk('public')->exists($path)) {
                    $publicHash = hash_file('sha256', Storage::disk('public')->path($path));
                    if (! is_string($publicHash) || ! hash_equals($privateHash, $publicHash)) {
                        throw new RuntimeException("Public and private receipt copies differ for payment {$payment->payment_id}; public source was retained.");
                    }
                    if (! Storage::disk('public')->delete($path)) {
                        throw new RuntimeException("Could not remove the public receipt for payment {$payment->payment_id}.");
                    }
                }
                $this->line("Secured receipt for payment {$payment->payment_id}.");
            }
        }, 'payment_id');

        return self::SUCCESS;
    }
}
