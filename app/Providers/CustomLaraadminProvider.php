<?php

namespace App\Providers;

use App\Helper\StoreOwnerHelper;
use App\Helper\CustomLAHelper;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class CustomLaraadminProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $loader = AliasLoader::getInstance();
        // For LaraAdmin Helper
        $loader->alias('CustomLAHelper', CustomLAHelper::class);
        $loader->alias('StoreOwnerHelper', StoreOwnerHelper::class);
    }
}
