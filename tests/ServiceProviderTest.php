<?php

use TheJano\AreebaPayment\Contracts\PaymentGateway;
use TheJano\AreebaPayment\Services\AreebaMpgsPayment;
use TheJano\AreebaPayment\Services\AreebaPayment;

it('merges the package config', function () {
    expect(config('areeba.driver'))->toBe('ixopay')
        ->and(config('areeba.mpgs.base_url'))->toBe('https://epayment.example.com');
});

it('resolves the services as singletons', function () {
    expect(app(AreebaPayment::class))->toBe(app(AreebaPayment::class))
        ->and(app(AreebaMpgsPayment::class))->toBe(app(AreebaMpgsPayment::class));
});

it('binds the ixopay driver by default', function () {
    expect(app(PaymentGateway::class))->toBeInstanceOf(AreebaPayment::class);
});

it('binds the mpgs driver when configured', function () {
    config()->set('areeba.driver', 'mpgs');

    expect(app(PaymentGateway::class))->toBeInstanceOf(AreebaMpgsPayment::class);
});
