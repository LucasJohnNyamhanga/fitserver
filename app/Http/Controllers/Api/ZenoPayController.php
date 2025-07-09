<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\ZenoPayService;
use App\Http\Controllers\Controller;

class ZenoPayController extends Controller
{
    protected $zenoPay;

    public function __construct(ZenoPayService $zenoPay)
    {
        $this->zenoPay = $zenoPay;
    }

    /**
     * Initiates a payment with ZenoPay.
     * Request body:
     * {
     *   "amount": 5000,
     *   "currency": "TZS",
     *   "reference": "TXN123456",
     *   "mobile": "255712345678",
     *   "description": "Mkopo payment"
     * }
     */
    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'currency' => 'required|string|in:TZS,USD',
            'reference' => 'required|string|max:50',
            'mobile' => 'required|string|min:10|max:15',
            'description' => 'nullable|string|max:255',
            'packageId' => 'required|exists:packages,id',
        ]);

        try {
            $result = $this->zenoPay->createPayment(
                $validated['amount'],
                $validated['currency'],
                $validated['reference'],
                $validated['mobile'],
                $validated['description'] ?? null
            );

            Payment::create([
                'reference' => $validated['reference'],
                'amount' => $validated['amount'],
                'status' => 'pending',
                'package_id' =>  $validated['packageId'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment request sent successfully.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function paymentCallback(Request $request)
    {
        $payment = Payment::where('reference', $request->reference)->first();

        if ($payment) {
            $payment->status = $request->status;
            $payment->transaction_id = $request->transaction_id;
            $payment->method = $request->payment_method;
            $payment->save();
        }
    
        return response()->json(['status' => 'callback_received']);
    }

}
