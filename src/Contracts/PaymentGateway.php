<?php

namespace TheJano\AreebaPayment\Contracts;

interface PaymentGateway
{
    public function initiatePayment(string $transactionId, string $amount, string $name);
    public function checkPaymentStatus(string $transactionId): array;
}