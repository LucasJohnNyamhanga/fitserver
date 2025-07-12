<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Services\ZenoPayService;

class ZenoPayWebhookController extends Controller
{
    protected ZenoPayService $zenoPay;

    public function __construct(ZenoPayService $zenoPay)
    {
        $this->zenoPay = $zenoPay;
    }

    public function handle(Request $request)
    {
        $data = $request->all();
        $orderId = $data['order_id'] ?? null;
        $status = strtolower($data['payment_status'] ?? '');

        if (!$orderId || $status !== 'completed') {
            return response()->json(['message' => 'Ignored or invalid status'], 200);
        }

        // We're using order_id as reference in DB
        $payment = Payment::where('reference', $orderId)->first();

        if (!$payment) {
            Log::warning("Payment not found for order ID: $orderId");
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Avoid double-processing
        if (strtoupper($payment->status) === 'COMPLETED') {
            return response()->json(['message' => 'Already completed'], 200);
        }

        // Pull full transaction details from ZenoPayService
        $response = $this->zenoPay->checkStatus($orderId);
        $transaction = $response['details'] ?? null;

        if (!$transaction || strtoupper($transaction['payment_status'] ?? '') !== 'COMPLETED') {
            return response()->json(['message' => 'Status not confirmed'], 200);
        }

        // Use previous values if missing in API
        $channel = $payment->channel;
        $reference = $payment->reference;

        $payment->update([
            'status' => 'COMPLETED',
            'transaction_id' => $transaction['transid'] ?? null,
            'channel' => $transaction['channel'] ?? $channel,
            'reference' => $transaction['reference'] ?? $reference,
            'completed_at' => now(),
        ]);

        Log::info("Payment $orderId marked as COMPLETED via webhook.");

        return response()->json(['message' => 'Payment updated'], 200);
    }
}
