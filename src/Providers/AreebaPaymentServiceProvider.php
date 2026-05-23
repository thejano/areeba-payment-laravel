<?php

namespace TheJano\AreebaPayment\Providers;

use Illuminate\Support\ServiceProvider;
use TheJano\AreebaPayment\Contracts\PaymentGateway;
use TheJano\AreebaPayment\Services\AreebaMpgsPayment;
use TheJano\AreebaPayment\Services\AreebaPayment;

class AreebaPaymentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/areeba.php' => config_path('areeba.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/areeba.php', 'areeba');

        $this->app->singleton(AreebaPayment::class, function ($app) {
            return AreebaPayment::make();
        });

        $this->app->singleton(AreebaMpgsPayment::class, function ($app) {
            return AreebaMpgsPayment::make();
        });

        $this->app->bind(PaymentGateway::class, function ($app) {
            return config('areeba.driver') === 'mpgs'
                ? AreebaMpgsPayment::make()
                : AreebaPayment::make();
        });
    }
}
