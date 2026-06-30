<?php

namespace TheJano\AreebaPayment\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionProperty;
use TheJano\AreebaPayment\Providers\AreebaPaymentServiceProvider;
use TheJano\AreebaPayment\Services\AreebaMpgsPayment;
use TheJano\AreebaPayment\Services\AreebaPayment;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        // The services cache a static singleton built from config. Reset it
        // before each test so config overrides take effect in isolation.
        $this->resetSingleton(AreebaPayment::class);
        $this->resetSingleton(AreebaMpgsPayment::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->resetSingleton(AreebaPayment::class);
        $this->resetSingleton(AreebaMpgsPayment::class);

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            AreebaPaymentServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.name', 'TestApp');

        $app['config']->set('areeba.driver', 'ixopay');
        $app['config']->set('areeba.api_key', 'test-api-key');
        $app['config']->set('areeba.username', 'ixopay-user');
        $app['config']->set('areeba.password', 'ixopay-pass');
        $app['config']->set('areeba.base_url', 'https://gateway.example.com/api/v3');
        $app['config']->set('areeba.language', 'en');
        $app['config']->set('areeba.currency', 'usd');
        $app['config']->set('areeba.transaction_prefix', 'TEST-');
        $app['config']->set('areeba.redirect_url', [
            'success'  => 'https://shop.test/success',
            'error'    => 'https://shop.test/error',
            'cancel'   => 'https://shop.test/cancel',
            'callback' => 'https://shop.test/callback',
        ]);

        $app['config']->set('areeba.mpgs', [
            'base_url'         => 'https://epayment.example.com',
            'api_version'      => '100',
            'merchant_id'      => 'MERCHANT1',
            'username'         => 'mpgs-user',
            'password'         => 'mpgs-pass',
            'currency'         => 'usd',
            'checkout_version' => '1.0.0',
            'redirect_url'     => [
                'return'  => 'https://shop.test/return',
                'cancel'  => 'https://shop.test/cancel',
                'timeout' => 'https://shop.test/timeout',
            ],
        ]);
    }

    protected function resetSingleton(string $class): void
    {
        $property = new ReflectionProperty($class, 'instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
}
