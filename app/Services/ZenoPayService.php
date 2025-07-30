<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Purchase;

class ZenoPayService
{
    protected string $baseUrl = 'https://zenoapi.com/api';

    /**
     * Create a mobile money payment request using ZenoPay.
     */
    public function createPayment(
        string $orderId,
        string $buyerEmail,
        string $buyerName,
        string $buyerPhone,
        float|int $amount,
        ?string $webhookUrl = null
    ): array {
        $apiKey = config('services.zenopay.token');
        $webhookUrl = $webhookUrl ?? $apiKey;

        $payload = [
            'order_id'    => $orderId,
            'buyer_email' => $buyerEmail,
            'buyer_name'  => $buyerName,
            'buyer_phone' => $buyerPhone,
            'amount'      => $amount,
        ];

        if ($webhookUrl) {
            $payload['webhook_url'] = $webhookUrl;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept'    => 'application/json',
            ])
                ->timeout(30)
                ->retry(3, 1000)
                ->post("{$this->baseUrl}/payments/mobile_money_tanzania", $payload);

            if ($response->failed()) {
                throw new \Exception('ZenoPay API Error: ' . $response->body());
            }

            return $response->json();

        } catch (\Exception $e) {
            throw new \Exception("ZenoPay request failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check the payment status from ZenoPay and update local DB.
     * Also creates a purchase record without checking for duplicates.
     */
    public function checkStatus(string $reference): array
    {
        $apiKey = config('services.zenopay.token');

        try {
            $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept'    => 'application/json',
                ])
                ->timeout(15) // Increased timeout from 10 to 15 seconds
                ->retry(3, 2000) // Retry up to 3 times with 2s delay
                ->get("{$this->baseUrl}/payments/order-status?order_id={$reference}");

            Log::info("ZenoPay status response for {$reference}: {$response->body()}");

            $responseData = json_decode($response->body(), true);

            if (
                $response->successful() &&
                isset($responseData['data']) &&
                is_array($responseData['data']) &&
                isset($responseData['data'][0])
            ) {
                $paymentInfo = $responseData['data'][0];

                // Update local DB record
                $payment = Payment::where('reference', $reference)->first();
                if ($payment) {
                    $payment->update([
                        'status'         => strtolower($paymentInfo['payment_status']),
                        'transaction_id' => $paymentInfo['transid'] ?? $payment->transaction_id,
                        'channel'        => $paymentInfo['channel'] ?? $payment->channel,
                        'amount'         => $paymentInfo['amount'] ?? $payment->amount,
                    ]);

                    // Create purchase WITHOUT checking if it already exists
                    // Assumes $payment has 'package_id' column
                    if (isset($payment->package_id)) {
                        Purchase::create([
                            'package_id' => $payment->package_id,
                            'user_id'    => Auth::id(),
                        ]);
                        Log::info("Purchase record created for user " . Auth::id() . " and package " . $payment->package_id);
                    }
                }

                return [
                    'status'  => strtolower($paymentInfo['payment_status']),
                    'details' => $paymentInfo,
                ];
            }

            Log::warning("ZenoPay unexpected response format for {$reference}");

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("ZenoPay request exception for {$reference}: {$e->getMessage()}");
        } catch (\Throwable $e) {
            Log::error("ZenoPay unknown error for {$reference}: {$e->getMessage()}");
        }

        return [
            'status'  => 'pending',
            'details' => null,
        ];
    }
}
