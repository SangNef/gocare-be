<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CODOrder;

class CODOrderController extends Controller
{
    public function trackingOrder($partner, Request $request)
    {
        switch ($partner) {
            case CODOrder::PARTNER_VTP:
                $orderCode = $request->DATA['ORDER_NUMBER'];
                $status = $request->DATA['ORDER_STATUS'];
                break;
            case CODOrder::PARTNER_GHTK:
                $orderCode = $request->label_id;
                $status = $request->status_id;
                break;
            default:
                $orderCode = '';
                $status = '';
                break;
        }
        if ($orderCode && $status) {
            $codOrder = CODOrder::where('order_code', $orderCode)
                ->where('partner', $partner)
                ->first();
            if ($codOrder) {
                $codOrder->status = $status;
                $codOrder->save();
            }
        }
        return response()->json('');
    }
}
