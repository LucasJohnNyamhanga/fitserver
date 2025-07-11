<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\ZenoPayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Payment $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function handle(ZenoPayService $zenoPay)
    {
        // Skip if payment already processed
        if ($this->payment->status !== 'pending') {
            return;
        }

        try {
            $response = $zenoPay->checkStatus($this->payment->reference);

            if ($response->status === 'COMPLETED') {
                $this->payment->update(['status' => 'completed']);
                Log::info("Payment {$this->payment->id} completed.");
                return; // done
            }

            // Increment retry count
            $this->payment->retries_count++;
            $retryCount = min($this->payment->retries_count, 5);

            if ($retryCount >= 5) {
                // Mark failed after max retries
                $this->payment->update(['status' => 'failed']);
                Log::warning("Payment {$this->payment->id} marked as failed after max retries.");
                return;
            }

            // Calculate delay with exponential backoff (in minutes)
            $delayMinutes = 2 ** $retryCount;

            $this->payment->update([
                'retries_count' => $this->payment->retries_count,
                'next_check_at' => now()->addMinutes($delayMinutes),
            ]);

            // Re-dispatch job delayed by $delayMinutes minutes
            self::dispatch($this->payment)->delay(now()->addMinutes($delayMinutes));

            Log::info("Payment {$this->payment->id} status still pending; will retry in {$delayMinutes} minutes.");

        } catch (\Throwable $e) {
            Log::error("Payment check failed for payment {$this->payment->id}: {$e->getMessage()}");
            // Optionally, re-dispatch with some delay or handle differently
        }
    }
}
