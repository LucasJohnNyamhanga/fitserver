<?php
namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\ZenoPayService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZenoPayController extends Controller
{
    protected ZenoPayService $zenoPay;

    public function __construct(ZenoPayService $zenoPay)
    {
        $this->zenoPay = $zenoPay;
    }

    /**
     * Initiates a payment via ZenoPay API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Example Request:
     * {
     *   "amount": 5000,
     *   "mobile": "0744123456",
     *   "reference": "TXN123456",
     *   "packageId": 1,
     *   "buyerEmail": "user@example.com", // optional
     *   "buyerName": "Lucas"              // optional
     * }
     */
    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'mobile' => 'required|string|min:10|max:15',
            'reference' => 'required|string|max:50|unique:payments,reference',
            'packageId' => 'required|exists:packages,id',
            'buyerEmail' => 'nullable|email',
            'buyerName' => 'nullable|string|max:100',
        ]);

        $buyerEmail = $validated['buyerEmail'] ?? 'datasofttanzania@gmail.com';
        $buyerName = $validated['buyerName'] ?? 'Anonymous User';
        $webhookUrl = env('ZENOPAY_CALLBACK_URL');// You must define this route separately

        try {
            // Call ZenoPay API
            $response = $this->zenoPay->createPayment(
                orderId: $validated['reference'],
                buyerEmail: $buyerEmail,
                buyerName: $buyerName,
                buyerPhone: $validated['mobile'],
                amount: $validated['amount'],
                webhookUrl: $webhookUrl
            );

            // Log API response for reference
            Log::info('ZenoPay initiated successfully', [
                'reference' => $validated['reference'],
                'mobile' => $validated['mobile'],
                'api_response' => $response
            ]);

            // Save to local DB
            Payment::create([
                'reference' => $validated['reference'],
                'amount' => $validated['amount'],
                'status' => 'pending',
                'package_id' => $validated['packageId'],
                'phone' => $validated['mobile'],
                'channel' => 'ZENOPAY',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully.',
                'data' => $response,
            ]);

        } catch (\Throwable $e) {
            Log::error('ZenoPay initiation failed', [
                'reference' => $validated['reference'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again later.',
            ], 500);
        }
    }
}
