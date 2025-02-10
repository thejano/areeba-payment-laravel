<?php

namespace TheJano\AreebaPayment\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use TheJano\AreebaPayment\Contracts\PaymentGateway;
use TheJano\AreebaPayment\Data\AreebaPaymentRequestData;
use TheJano\AreebaPayment\Helpers\TransactionHelper;

class AreebaPayment implements PaymentGateway
{
    private static ?AreebaPayment $instance = null;

    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $apiKey;

    private function __construct()
    {
        $config = config('areeba');
        $this->baseUrl = $config['base_url'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->apiKey = $config['api_key'];
    }

    public static function make(): AreebaPayment
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function initiatePayment(string $transactionId, string $amount, string $name, ?string $currency = null): AreebaPaymentRequestData
    {
        $requestData = $this->generateTransactionData($transactionId, $amount, $name);
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->getTransactionUrl(), $requestData);

        return new AreebaPaymentRequestData($response->json());
    }

    public function checkPaymentStatus(string $transactionId): array
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->get($this->getTransactionStatusUrl($transactionId));

        return $response->json();
    }

    private function generateTransactionData(string $transactionId, string $amount, string $name, ?string $currency = null): array
    {
        $name = Str::of($name)->explode(' ');
        $transactionId = self::getFullTransactionId($transactionId);
        return [
            'merchantTransactionId' => $transactionId,
            'amount' => $amount,
            'currency' => $currency ?? Str::upper(config('areeba.currency')),
            'successUrl' => $this->getRedirectUrl(config('areeba.redirect_url.success'), $transactionId),
            'cancelUrl' => $this->getRedirectUrl(config('areeba.redirect_url.cancel'), $transactionId),
            'errorUrl' => $this->getRedirectUrl(config('areeba.redirect_url.error'), $transactionId),
            'callbackUrl' => $this->getRedirectUrl(config('areeba.redirect_url.callback'), $transactionId),
            'customer' => [
                'firstName' => $name->first(),
                'lastName' => $name->last(),
                'company' => config('app.name'),
                'ipAddress' => request()->ip(),
            ],
            'language' => config('areeba.language'),
        ];
    }


    private function getTransactionUrl(): string
    {
        return "{$this->baseUrl}/transaction/{$this->apiKey}/debit";
    }

    private function getTransactionStatusUrl(string $transactionId): string
    {
        $transactionId = TransactionHelper::getFullTransactionId($transactionId);
        return "{$this->baseUrl}/status/{$this->apiKey}/getByMerchantTransactionId/{$transactionId}";
    }

    public static function getFullTransactionId(string $transactionId): string
    {
        $transactionId = TransactionHelper::cleanPrefix($transactionId);
        return config('areeba.transaction_prefix') . $transactionId;
    }

    public function getRedirectUrl(string $url, string $transactionId): string
    {
        return "{$url}?transactionId={$transactionId}";
    }
}