<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        ?string $webhookUrl = null //Implicitly nullable parameters are deprecated.
    ): array {
        $apiKey = env('ZENOPAY_API_KEY');

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
            ->timeout(30) // Timeout in seconds
            ->retry(3, 1000) // Retry 3 times with 1s delay
            ->post("{$this->baseUrl}/payments/mobile_money_tanzania", $payload);

            if ($response->failed()) {
                throw new \Exception('ZenoPay API Error: ' . $response->body());
            }

            return $response->json();

        } catch (\Exception $e) {
            throw new \Exception("ZenoPay request failed: " . $e->getMessage(), 0, $e);
        }
    }
}
