<?php

use Illuminate\Support\Facades\Http;
use TheJano\AreebaPayment\Data\AreebaPaymentRequestData;
use TheJano\AreebaPayment\Services\AreebaPayment;

const IXOPAY_DEBIT_URL = 'https://gateway.example.com/api/v3/transaction/test-api-key/debit';

it('posts the debit request and maps the response', function () {
    Http::fake([
        IXOPAY_DEBIT_URL => Http::response([
            'success'     => true,
            'uuid'        => 'uuid-1',
            'returnType'  => 'REDIRECT',
            'redirectUrl' => 'https://gateway.example.com/redirect/uuid-1',
        ], 200),
    ]);

    $result = AreebaPayment::make()->initiatePayment('ORDER-1', '25.00', 'Jane Doe');

    expect($result)->toBeInstanceOf(AreebaPaymentRequestData::class)
        ->and($result->success)->toBeTrue()
        ->and($result->uuid)->toBe('uuid-1')
        ->and($result->getPaymentUrl())->toBe('https://gateway.example.com/redirect/uuid-1');
});

it('sends the expected transaction payload', function () {
    Http::fake([
        IXOPAY_DEBIT_URL => Http::response(['success' => true], 200),
    ]);

    AreebaPayment::make()->initiatePayment('ORDER-1', '25.00', 'Jane Doe');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === IXOPAY_DEBIT_URL
            && $request->hasHeader('Authorization')
            && $body['merchantTransactionId'] === 'TEST-ORDER-1'
            && $body['amount'] === '25.00'
            && $body['currency'] === 'USD'
            && $body['successUrl'] === 'https://shop.test/success?transactionId=TEST-ORDER-1'
            && $body['cancelUrl'] === 'https://shop.test/cancel?transactionId=TEST-ORDER-1'
            && $body['errorUrl'] === 'https://shop.test/error?transactionId=TEST-ORDER-1'
            && $body['callbackUrl'] === 'https://shop.test/callback?transactionId=TEST-ORDER-1'
            && $body['customer']['firstName'] === 'Jane'
            && $body['customer']['lastName'] === 'Doe'
            && $body['customer']['company'] === 'TestApp'
            && $body['language'] === 'en';
    });
});

it('fetches the transaction status by merchant transaction id', function () {
    $statusUrl = 'https://gateway.example.com/api/v3/status/test-api-key/getByMerchantTransactionId/TEST-ORDER-1';

    Http::fake([
        $statusUrl => Http::response(['success' => true, 'transactionStatus' => 'PENDING'], 200),
    ]);

    $status = AreebaPayment::make()->checkPaymentStatus('ORDER-1');

    expect($status['transactionStatus'])->toBe('PENDING');
    Http::assertSent(fn ($request) => $request->url() === $statusUrl);
});

it('prefixes the transaction id and avoids double prefixing', function () {
    expect(AreebaPayment::getFullTransactionId('ORDER-1'))->toBe('TEST-ORDER-1')
        ->and(AreebaPayment::getFullTransactionId('TEST-ORDER-1'))->toBe('TEST-ORDER-1');
});

it('appends the transaction id to redirect urls', function () {
    expect(AreebaPayment::make()->getRedirectUrl('https://shop.test/done', 'TEST-ORDER-1'))
        ->toBe('https://shop.test/done?transactionId=TEST-ORDER-1');
});
