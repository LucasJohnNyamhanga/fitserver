<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\ZenoPayService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
     */
    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100',
            'mobile' => 'required|string|min:10|max:15',
            'reference' => 'required|string|max:50|unique:payments,reference',
            'kifurushi_id' => 'required|exists:kifurushis,id',
            'buyerEmail' => 'nullable|email',
            'buyerName' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $buyerEmail = $request->buyerEmail ?? 'datasofttanzania@gmail.com';
        $buyerName = $request->buyerName ?? 'Anonymous User';
        $webhookUrl = env('ZENOPAY_CALLBACK_URL'); // Ensure this route is secured!

        try {
            // Call ZenoPay API
            $response = $this->zenoPay->createPayment(
                orderId: $request->reference,
                buyerEmail: $buyerEmail,
                buyerName: $buyerName,
                buyerPhone: $request->mobile,
                amount: $request->amount,
                webhookUrl: $webhookUrl
            );

            $channel = $this->getMtandaoFromNumber($request->mobile);

            // Save to local DB
            $payment = Payment::create([
                'reference'     => $request->reference,
                'amount'        => $request->amount,
                'status'        => 'pending',
                'kifurushi_id'  => $request->kifurushi_id,
                'phone'         => $request->mobile,
                'user_id'       => $user->id,
                'channel'       => $channel,
            ]);

            Log::info('ZenoPay initiated successfully', [
                'reference' => $payment->reference,
                'mobile' => $payment->phone,
                'user_id' => $user->id,
                'api_response' => $response,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully.',
                'data' => [
                    'zenopay' => $response,
                    'reference' => $payment->reference,
                    'payment_id' => $payment->id,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('ZenoPay initiation failed', [
                'reference' => $request->reference ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again later.',
            ], 500);
        }
    }

    /**
     * Detect mobile network operator from phone number
     */
    private function getMtandaoFromNumber(string $number): string
    {
        // Remove non-numeric characters
        $number = preg_replace('/\D+/', '', $number);

        // Standardize number to start with 0
        if (str_starts_with($number, '255')) {
            $number = '0' . substr($number, 3);
        }

        if (!preg_match('/^0\d{9}$/', $number)) {
            return 'Mtandao';
        }

        $prefix = substr($number, 0, 3);

        return match ($prefix) {
            '075', '076', '074'         => 'Mpesa',       // Vodacom
            '078', '068', '069', '079'  => 'AirtelMoney', // Airtel
            '071', '077', '065'         => 'TigoPesa',    // Tigo
            '062', '061'                => 'HaloPesa',    // Halotel
            '073'                       => 'TTCLPesa',    // TTCL
            default                     => 'Mtandao',     // Unknown/Other
        };
    }
}
