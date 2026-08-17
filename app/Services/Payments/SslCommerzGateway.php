<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class SslCommerzGateway
{
    public function baseUrl(): string
    {
        return config('services.sslcommerz.sandbox', true)
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function storeId(): string
    {
        return config('services.sslcommerz.store_id', '');
    }

    public function storePassword(): string
    {
        return config('services.sslcommerz.store_password', '');
    }

    public function isConfigured(): bool
    {
        return $this->storeId() !== '' && $this->storePassword() !== '';
    }

    public function generateTransactionId(): string
    {
        return 'TF' . time() . random_int(1000, 9999);
    }

    public function initSession(array $params): array
    {
        $payload = array_merge([
            'store_id' => $this->storeId(),
            'store_passwd' => $this->storePassword(),
            'total_amount' => '0',
            'currency' => 'BDT',
            'tran_id' => $this->generateTransactionId(),
            'product_category' => 'general',
            'product_profile' => 'general',
            'ship_name' => $params['cus_name'] ?? '',
            'ship_add1' => config('services.sslcommerz.shipping_address', 'Dhaka'),
            'ship_city' => config('services.sslcommerz.shipping_city', 'Dhaka'),
            'ship_state' => config('services.sslcommerz.shipping_state', 'Dhaka'),
            'ship_postcode' => config('services.sslcommerz.shipping_postcode', '1000'),
            'ship_country' => config('services.sslcommerz.shipping_country', 'Bangladesh'),
        ], $params);

        $response = Http::asForm()
            ->timeout(30)
            ->post($this->baseUrl() . '/gwprocess/v4/api.php', $payload);

        return $response->json() ?? [];
    }

    public function validate(string $sessionKey, string $transactionId): array
    {
        $response = Http::asForm()
            ->timeout(30)
            ->post($this->baseUrl() . '/validator/api/merchantTransIDvalidationAPI.php', [
                'sessionkey' => $sessionKey,
                'tran_id' => $transactionId,
                'store_id' => $this->storeId(),
                'store_passwd' => $this->storePassword(),
            ]);

        return $response->json() ?? [];
    }
}
