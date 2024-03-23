<?php

namespace App\Repositories;

use App\Models\AttributeValue;
use App\Models\Customer;
use App\Models\Group;
use App\Models\SocialAccount;
use Laravel\Socialite\Contracts\Provider;

class AttributeValueRepository
{
    public function getAttrs($attrValues = [])
    {
        $attrValue = AttributeValue::whereIn('id', $attrValues)->get();
        $attrs = [];
        foreach ($attrValue as $value) {
            $attrs[] = [
                'name' => $value->attr->name,
                'values' => $value->attr->values->toArray(),
            ];
        }

        return $attrs;
    }
}
