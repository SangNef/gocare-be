<?php

namespace App\Http\Middleware\ApiV2;

use App\Models\Product;
use App\Scopes\ApiV2\ProductScope;
use Closure;

class ProductApi
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
        Product::addGlobalScope(new ProductScope());
        return $next($request);
    }
}
