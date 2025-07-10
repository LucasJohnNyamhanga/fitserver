<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ZenoPayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $apiKey = env('ZENOPAY_API_KEY');
        if ($request->header('x-api-key') !== $apiKey) {
            Log::warning('Unauthorized ZenoPay webhook');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        Log::info('ZenoPay Webhook Received', $data);

        // Example: Update payment status in DB
        $orderId = $data['order_id'] ?? null;
        $status = $data['payment_status'] ?? null;

        if ($orderId && $status === 'COMPLETED') {
            // Update payment record
            DB::table('payments')->where('order_id', $orderId)->update([
                'status' => 'COMPLETED',
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Received'], 200);
    }
}
