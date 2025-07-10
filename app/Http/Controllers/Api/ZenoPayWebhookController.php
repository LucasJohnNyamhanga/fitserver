<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZenoPayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // ✅ Authenticate webhook
        $apiKey = env('ZENOPAY_API_KEY');
        if ($request->header('x-api-key') !== $apiKey) {
            Log::warning('Unauthorized ZenoPay webhook access attempt', [
                'headers' => $request->headers->all(),
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        Log::info('ZenoPay Webhook Received', $data);

        $orderId = $data['order_id'] ?? null;
        $status = $data['payment_status'] ?? null;
        $transactionId = $data['reference'] ?? null;

        if ($orderId && $status === 'COMPLETED') {
            $updated = DB::table('payments')->where('reference', $orderId)->update([
                'status' => 'completed',
                'transaction_id' => $transactionId,
                'updated_at' => now(),
            ]);

            Log::info("Payment updated via webhook", [
                'reference' => $orderId,
                'updated' => $updated
            ]);
        }

        return response()->json(['message' => 'Received'], 200);
    }
}
