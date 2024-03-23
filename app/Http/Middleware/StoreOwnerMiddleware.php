<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Helper\StoreOwnerHelper;

class StoreOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $urls = StoreOwnerHelper::excludeRoutes();
        if (Auth::check() && Auth::user()->haveRoleMustBeExcludeFromRoutes()) {
            foreach ($urls as $url) {
                $url = '/admin/' . $url;
                if ($request->getRequestUri() == $url) {
                    return redirect('/admin');
                }
            }
        }

        return $next($request);
    }
}
