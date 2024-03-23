<?php

namespace App\Observes;

use App\Models\AccessToken;

class AccessTokenObserve
{
    public function creating(AccessToken $accessToken)
    {
        $accessToken->api_key = app(\App\Services\Generator::class)->generate(50);
    }
}
