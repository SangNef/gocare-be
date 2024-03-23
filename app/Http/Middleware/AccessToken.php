<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class AccessToken
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
        switch (true) {
            case $request->is('api/vtp/tracking-order'):
                $token = $request->TOKEN;
                break;
            case $request->is('api/ghtk/tracking-order'):
                $token = $request->token;
                break;
            default:
                $token = $request->header('AccessToken');
        }
        if (!$token || !$this->isValidToken($token)) {
            return response()->json(['unauthorized'], 401);
        }

        return $next($request);
    }

    protected function isValidToken($token)
    {
        return DB::table('accesstokens')->where('api_key', $token)
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->exists();
    }
}
