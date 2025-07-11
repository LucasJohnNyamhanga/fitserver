<?php
namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\ZenoPayService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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

            $user = Auth::user();

            // Log API response for reference
            Log::info('ZenoPay initiated successfully', [
                'reference' => $validated['reference'],
                'mobile' => $validated['mobile'],
                'api_response' => $response
            ]);
            
            $channel = $this->getMtandaoFromNumber($validated['mobile']);

            // Save to local DB
            Payment::create([
                'reference' => $validated['reference'],
                'amount' => $validated['amount'],
                'status' => 'pending',
                'package_id' => $validated['packageId'],
                'phone' => $validated['mobile'],
                'user_id' => $user->id,
                'channel' => $channel,
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
    
    function getMtandaoFromNumber(string $number): string
    {
        // Normalize number (remove +255 or leading 255)
        $number = preg_replace('/^\+?255/', '0', $number);
    
        $prefix = substr($number, 0, 4);
    
        return match ($prefix) {
            '0754', '0755', '0756', '0757', '0758' => 'mpesa',
            '0783', '0784', '0785', '0786', '0787', '0788', '0789' => 'airtelmoney',
            '0655', '0656', '0657', '0658', '0659' => 'tigopesa',
            '0683', '0684', '0685', '0686', '0687' => 'halopesa',
            '0673', '0674', '0675', '0676', '0677' => 'ttcl',
            default => 'unknown',
        };
    }

}
