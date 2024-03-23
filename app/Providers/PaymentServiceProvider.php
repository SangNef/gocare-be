<?php

namespace App\Providers;

use App\Helper\StoreOwnerHelper;
use App\Helper\CustomLAHelper;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        foreach (config('payment.providers') as $name => $provider) {
            app()->bind('payment.' . $name, function () use ($name, $provider) {
                if (@$provider['class'] && class_exists($provider['class'])) {
                    return app($provider['class']);
                } else if (class_exists('\App\Services\Payments\\' . ucfirst($name) . 'Service')) {
                    return app('\App\Services\Payments\\' . ucfirst($name) . 'Service');
                }
                throw new \Exception('class not exists');
            });
        }
        
        if (config('payment.provider')) {
            app()->bind('payment', function () {
                return app('payment.' . config('payment.provider'));
            });
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        
    }
}
