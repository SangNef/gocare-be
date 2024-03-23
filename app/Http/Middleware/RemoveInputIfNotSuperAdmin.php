<?php

namespace App\Http\Middleware;

use Closure;

class RemoveInputIfNotSuperAdmin
{
    protected $inputs = [
        'debt_total',
        'last_balance'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->isChairmanUser()) {
            $input = $request->all();
            array_walk_recursive($input, function (&$input, $key) use ($request) {
                if (in_array($key, $this->inputs)) {
                    $request->offsetUnset($key);
                }
            });
        }
        return $next($request);
    }
}
