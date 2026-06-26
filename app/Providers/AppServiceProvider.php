<?php

namespace App\Providers;

use App\Payment\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            $manager = new PaymentGatewayManager;

            $gateways = config('payment.gateways', []);
            $credentials = config('payment.credentials', []);

            foreach ($gateways as $method => $gatewayClass) {
                $gateway = new $gatewayClass($credentials[$method] ?? []);
                $manager->register($method, $gateway);
            }

            return $manager;
        });
    }

    public function boot(): void
    {
        //
    }
}
