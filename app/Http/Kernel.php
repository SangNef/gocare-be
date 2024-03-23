<?php

namespace App\Http;

use App\Http\Middleware\NumberInput;
use App\Http\Middleware\RemoveInputIfNotSuperAdmin;
use App\Http\Middleware\XSS;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Barryvdh\Cors\HandleCors::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            XSS::class,
            NumberInput::class,
            RemoveInputIfNotSuperAdmin::class,
            \App\Http\Middleware\StoreOwnerMiddleware::class
        ],

        'api' => [
            'throttle:60,1',
            NumberInput::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'can' => \Illuminate\Foundation\Http\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'localization' => \App\Http\Middleware\Localization::class,
        'access' => \App\Http\Middleware\AccessToken::class,
        'api-v2' => \App\Http\Middleware\ApiV2\ProductApi::class,
        'jwt.verify' => \App\Http\Middleware\JWTMiddleware::class
    ];
}
