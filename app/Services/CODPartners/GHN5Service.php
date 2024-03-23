<?php

namespace App\Services\CODPartners;

use App\Exceptions\CODException;
use App\Models\Address;
use App\Models\CODOrder;
use App\Models\District;
use App\Models\Ward;
use GuzzleHttp\Exception\ClientException;
use App\Services\CODPartners\Shipping;
use App\Models\Config;
use App\Models\Order;
use App\Models\WarrantyOrder;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class GHN5Service extends GHNService
{
    const NAME = CODOrder::PARTNER_GHN_5;
}
