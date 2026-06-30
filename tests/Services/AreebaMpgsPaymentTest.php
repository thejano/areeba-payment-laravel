<?php

use Illuminate\Support\Facades\Http;
use TheJano\AreebaPayment\Data\AreebaPaymentRequestData;
use TheJano\AreebaPayment\Services\AreebaMpgsPayment;

const MPGS_SESSION_URL = 'https://epayment.example.com/api/rest/version/100/merchant/MERCHANT1/session';

function fakeMpgsSuccess(): void
{
    Http::fake([
        MPGS_SESSION_URL => Http::response([
            'result'  => 'SUCCESS',
            'session' => ['id' => 'SESSION123'],
        ], 201),
    ]);
}

it('returns success data with a redirect url', function () {
    fakeMpgsSuccess();

    $result = AreebaMpgsPayment::make()->initiatePayment('ORDER-1', '10.00', 'Jane Doe');

    expect($result)->toBeInstanceOf(AreebaPaymentRequestData::class)
        ->and($result->success)->toBeTrue()
        ->and($result->purchaseId)->toBe('SESSION123')
        ->and($result->returnType)->toBe('REDIRECT')
        ->and($result->redirectUrl)->toBe('https://epayment.example.com/checkout/pay/SESSION123?checkoutVersion=1.0.0')
        ->and($result->errorMessage)->toBeNull();
});

it('sends the expected session payload', function () {
    fakeMpgsSuccess();

    AreebaMpgsPayment::make()->initiatePayment('ORDER-1', '10.00', 'Jane Doe');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === MPGS_SESSION_URL
            && $request->hasHeader('Authorization')
            && $body['apiOperation'] === 'INITIATE_CHECKOUT'
            && $body['interaction']['operation'] === 'PURCHASE'
            && $body['interaction']['returnUrl'] === 'https://shop.test/return'
            && $body['interaction']['cancelUrl'] === 'https://shop.test/cancel'
            && $body['interaction']['timeoutUrl'] === 'https://shop.test/timeout'
            && $body['order']['id'] === 'ORDER-1'
            && $body['order']['amount'] === '10.00'
            && $body['order']['currency'] === 'USD'
            && $body['customer']['firstName'] === 'Jane'
            && $body['customer']['lastName'] === 'Doe'
            && $body['transaction']['source'] === 'INTERNET';
    });
});

it('uses an explicit currency when provided', function () {
    fakeMpgsSuccess();

    AreebaMpgsPayment::make()->initiatePayment('ORDER-1', '10.00', 'Jane Doe', 'EUR');

    Http::assertSent(fn ($request) => $request->data()['order']['currency'] === 'EUR');
});

it('sets a null last name for single word names', function () {
    fakeMpgsSuccess();

    AreebaMpgsPayment::make()->initiatePayment('ORDER-1', '10.00', 'Cher');

    Http::assertSent(function ($request) {
        $customer = $request->data()['customer'];

        return $customer['firstName'] === 'Cher' && $customer['lastName'] === null;
    });
});

it('returns failure data with the gateway error message', function () {
    Http::fake([
        MPGS_SESSION_URL => Http::response([
            'result' => 'ERROR',
            'error'  => ['explanation' => 'Invalid merchant'],
        ], 400),
    ]);

    $result = AreebaMpgsPayment::make()->initiatePayment('ORDER-1', '10.00', 'Jane Doe');

    expect($result->success)->toBeFalse()
        ->and($result->purchaseId)->toBeNull()
        ->and($result->returnType)->toBeNull()
        ->and($result->redirectUrl)->toBeNull()
        ->and($result->errorMessage)->toBe('Invalid merchant');
});

it('falls back to an http status error message when none is given', function () {
    Http::fake([
        MPGS_SESSION_URL => Http::response([], 500),
    ]);

    $result = AreebaMpgsPayment::make()->initiatePayment('ORDER-1', '10.00', 'Jane Doe');

    expect($result->success)->toBeFalse()
        ->and($result->errorMessage)->toContain('HTTP 500');
});

it('exposes the session id through getSessionId', function () {
    fakeMpgsSuccess();

    expect(AreebaMpgsPayment::make()->getSessionId('ORDER-1', '10.00', 'Jane Doe'))->toBe('SESSION123');
});

it('exposes the hosted checkout url through getPaymentLink', function () {
    fakeMpgsSuccess();

    expect(AreebaMpgsPayment::make()->getPaymentLink('ORDER-1', '10.00', 'Jane Doe'))
        ->toBe('https://epayment.example.com/checkout/pay/SESSION123?checkoutVersion=1.0.0');
});

it('returns the decoded order response from checkPaymentStatus', function () {
    $orderUrl = 'https://epayment.example.com/api/rest/version/100/merchant/MERCHANT1/order/ORDER-1';

    Http::fake([
        $orderUrl => Http::response(['result' => 'SUCCESS', 'order' => ['id' => 'ORDER-1']], 200),
    ]);

    $status = AreebaMpgsPayment::make()->checkPaymentStatus('ORDER-1');

    expect($status['result'])->toBe('SUCCESS')
        ->and($status['order']['id'])->toBe('ORDER-1');
});
