<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZenoPayService
{
    protected $baseUrl = 'https://api.zenopay.co.tz/v1';

    public function createPayment($amount, $currency, $reference, $customerMobile, $description = null)
    {
        $token = env('ZENOPAY_API_TOKEN');

        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'customer' => [
                'mobile_number' => $customerMobile,
            ],
            'description' => $description ?? 'ZenoPay Payment',
            'callback_url' => env('ZENOPAY_CALLBACK_URL', route('zenopay.callback', [], true)),
        ];

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->post("{$this->baseUrl}/invoices", $payload);

            Log::info('ZenoPay Request Sent', $payload);
            Log::info('ZenoPay Response', $response->json());

            if ($response->failed()) {
                throw new \Exception('ZenoPay API Error: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('ZenoPay HTTP Error: ' . $e->getMessage());
            throw new \Exception("Payment request failed. Try again later.");
        }
    }
}
