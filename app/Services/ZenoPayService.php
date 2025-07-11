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
        $apiKey = env('ZENOPAY_API_KEY');
        $webhookUrl = $webhookUrl ?? env('ZENOPAY_CALLBACK_URL'); // ✅ Use passed param or fallback to .env

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
        $response = Http::withToken(config('services.zenopay.token'))
            ->get("https://zenoapi.com/api/payments/$reference/status");

        if ($response->successful() && isset($response['data']['status'])) {
            return (object)[
                'status' => $response['data']['status'],
                'details' => $response['data'] ?? null,
            ];
        }

        // Fallback if response is invalid or missing data
        return (object)[
            'status' => 'pending',
            'details' => null,
        ];
    }

}
