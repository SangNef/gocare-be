<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Group;
use App\Models\SocialAccount;
use App\Models\Store;
use Laravel\Socialite\Contracts\Provider;

class SocialAccountRepository
{
    public function createOrGetUser(Provider $provider)
    {
        $providerUser = $provider->user();
        $providerUserId = $providerUser->getId();
        $providerUserEmail = $providerUser->getEmail();
        $providerName = class_basename($provider);
        $name = $providerUser->getName();

        $account = SocialAccount::where('provider', $providerName)
            ->where('provider_id', $providerUserId)
            ->first();
        if ($account && $account->customer) {
            return $account->customer;
        }

        $account = SocialAccount::create([
            'provider_id' => $providerUserId,
            'provider' => $providerName
        ]);
        $customerGroup = Group::getFECustomerGroup();
        $store = Store::getDefaultStore();
        if (Customer::where('email', $providerUserEmail)->exists()) {
            $providerUserEmail = '';
        }
        $customer = Customer::create([
            'email' => $providerUserEmail ?: 'unknow_'.uniqid().'@fqs.com',
            'name' =>  $name,
            'username' => $providerUserId,
            'group_id' => $customerGroup ? $customerGroup->id : 16,
            'store_id' => @$store->id
        ]);
        $account->customer()->associate($customer);
        $account->save();

        return $customer;
    }
}
