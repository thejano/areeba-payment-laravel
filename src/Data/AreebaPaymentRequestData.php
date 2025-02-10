<?php

namespace TheJano\AreebaPayment\Data;

use TheJano\AreebaPayment\Traits\CastableToArrayTrait;

class AreebaPaymentRequestData
{
    use CastableToArrayTrait;

    public bool $success;
    public ?string $uuid;
    public ?string $purchaseId;
    public ?string $returnType;
    public ?string $redirectUrl;
    public ?string $paymentMethod;
    public ?string $errorMessage;
    public ?int $errorCode;

    public function __construct(array $response)
    {
        $this->success = $response['success'] ?? false;
        $this->uuid = $response['uuid'] ?? null;
        $this->purchaseId = $response['purchaseId'] ?? null;
        $this->returnType = $response['returnType'] ?? null;
        $this->redirectUrl = $response['redirectUrl'] ?? null;
        $this->paymentMethod = $response['paymentMethod'] ?? null;
        $this->errorMessage = $response['errorMessage'] ?? null;
        $this->errorCode = $response['errorCode'] ?? null;
    }

    public function getPaymentUrl()
    {
        return $this->redirectUrl;
    }
}
