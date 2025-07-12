<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        ?string $webhookUrl = null // ✅ Explicit nullable
    ): array {
        $apiKey = config('services.zenopay.token');
        $webhookUrl = $webhookUrl ?? $apiKey; // ✅ Use passed param or fallback to .env

        $payload = [
            'order_id' => $orderId,
            'buyer_email' => $buyerEmail,
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'amount' => $amount,
        ];

        if ($webhookUrl) {
            $payload['webhook_url'] = $webhookUrl;
        }
        

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
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

    public function checkStatus(string $reference): object
    {
        $apiKey = config('services.zenopay.token');
    
        try {
            $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept'    => 'application/json',
                ])
                ->timeout(10)
                ->get("{$this->baseUrl}/payments/order-status?order_id={$reference}");
    
            Log::info("ZenoPay status response for {$reference}: {$response->body()}");
    
            if ($response->successful()) {
                $responseData = $response->json();
    
                if (!empty($responseData['data']) && is_array($responseData['data'])) {
                    $paymentInfo = $responseData['data'][0]; // first item in data array
    
                    if (isset($paymentInfo['payment_status'])) {
                        // Update database here
                        $payment = \App\Models\Payment::where('reference', $reference)->first();
    
                        if ($payment) {
                            $payment->status = strtolower($paymentInfo['payment_status']);
                            $payment->transaction_id = $paymentInfo['transid'] ?? null;
                            $payment->channel = $paymentInfo['channel'] ?? $payment->channel;
                            $payment->amount = $paymentInfo['amount'] ?? $payment->amount;
                            $payment->save();
                        } else {
                            Log::warning("Payment with reference {$reference} not found in database.");
                        }
    
                        return (object)[
                            'status'  => strtolower($paymentInfo['payment_status']),
                            'details' => $paymentInfo,
                        ];
                    }
                }
            }
    
            Log::warning("ZenoPay returned unexpected response format for {$reference}");
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("ZenoPay request exception for {$reference}: {$e->getMessage()}");
        } catch (\Throwable $e) {
            Log::error("ZenoPay unknown error for {$reference}: {$e->getMessage()}");
        }
    
        return (object)[
            'status'  => 'PENDING',
            'details' => null,
        ];
    }

}
