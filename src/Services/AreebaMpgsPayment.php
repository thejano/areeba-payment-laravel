<?php

namespace TheJano\AreebaPayment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TheJano\AreebaPayment\Contracts\PaymentGateway;
use TheJano\AreebaPayment\Data\AreebaPaymentRequestData;

class AreebaMpgsPayment implements PaymentGateway
{
    private static ?AreebaMpgsPayment $instance = null;

    protected string $baseUrl;
    protected string $apiVersion;
    protected string $merchantId;
    protected string $username;
    protected string $password;
    protected string $currency;
    protected string $checkoutVersion;

    private function __construct()
    {
        $config = config('areeba.mpgs');
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->apiVersion = (string) ($config['api_version'] ?? '100');
        $this->merchantId = (string) ($config['merchant_id'] ?? '');
        $this->username = (string) ($config['username'] ?? '');
        $this->password = (string) ($config['password'] ?? '');
        $this->currency = (string) ($config['currency'] ?? 'USD');
        $this->checkoutVersion = (string) ($config['checkout_version'] ?? '1.0.0');
    }

    public static function make(): AreebaMpgsPayment
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function initiatePayment(string $transactionId, string $amount, string $name, ?string $currency = null): AreebaPaymentRequestData
    {
        $payload = $this->generateSessionPayload($transactionId, $amount, $name, $currency);

        $response = $this->http()->post($this->sessionUrl(), $payload);
        $body = $response->json() ?? [];

        $successful = $response->successful() && (($body['result'] ?? null) === 'SUCCESS');
        $sessionId = $body['session']['id'] ?? null;

        if (!$successful) {
            Log::warning('Areeba MPGS createSession failed', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
        }

        return new AreebaPaymentRequestData([
            'success'      => $successful,
            'purchaseId'   => $sessionId,
            'returnType'   => $successful ? 'REDIRECT' : null,
            'redirectUrl'  => $successful && $sessionId ? $this->hostedCheckoutUrl($sessionId) : null,
            'errorMessage' => $successful
                ? null
                : ($body['error']['explanation']
                    ?? $body['error']['cause']
                    ?? sprintf('Payment gateway request failed (HTTP %d).', $response->status())),
        ]);
    }

    public function getSessionId(string $transactionId, string $amount, string $name, ?string $currency = null): ?string
    {
        return $this->initiatePayment($transactionId, $amount, $name, $currency)->purchaseId;
    }

    public function getPaymentLink(string $transactionId, string $amount, string $name, ?string $currency = null): ?string
    {
        return $this->initiatePayment($transactionId, $amount, $name, $currency)->redirectUrl;
    }

    public function checkPaymentStatus(string $transactionId): array
    {
        $response = $this->http()->get($this->orderUrl($transactionId));
        return $response->json() ?? [];
    }

    private function generateSessionPayload(string $transactionId, string $amount, string $name, ?string $currency = null): array
    {
        $parts = Str::of($name)->trim()->explode(' ');

        return [
            'apiOperation' => 'INITIATE_CHECKOUT',
            'interaction' => [
                'operation' => 'PURCHASE',
                'merchant' => [
                    'name' => config('app.name'),
                ],
                'returnUrl' => (string) config('areeba.mpgs.redirect_url.success', ''),
            ],
            'order' => [
                'id'          => $transactionId,
                'amount'      => $amount,
                'currency'    => $currency ?? Str::upper($this->currency),
                'description' => 'Order ' . $transactionId,
            ],
            'customer' => [
                'firstName' => $parts->first(),
                'lastName'  => $parts->count() > 1 ? $parts->last() : null,
            ],
            'transaction' => [
                'source' => 'INTERNET',
            ],
        ];
    }

    private function http()
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->asJson();
    }

    private function merchantBaseUrl(): string
    {
        return "{$this->baseUrl}/api/rest/version/{$this->apiVersion}/merchant/{$this->merchantId}";
    }

    private function sessionUrl(): string
    {
        return $this->merchantBaseUrl() . '/session';
    }

    private function orderUrl(string $orderId): string
    {
        return $this->merchantBaseUrl() . '/order/' . rawurlencode($orderId);
    }

    private function hostedCheckoutUrl(string $sessionId): string
    {
        return "{$this->baseUrl}/checkout/pay/{$sessionId}?checkoutVersion={$this->checkoutVersion}";
    }
}
