<?php

namespace App\Traits\Order;

use App\Models\PaymentHistory;
use App\Services\Payments\ViettelMoneyService;
use App\Services\Payments\VnpayService;
use Illuminate\Http\JsonResponse;

trait OrderTrait
{
    private function handlePaymentViettel(PaymentHistory $paymentHistory, int $total): JsonResponse
    {
        $service = app()->make(ViettelMoneyService::class);
        $redirect = $service->createRedirectLinkForOrder([
            'order_id' => $paymentHistory->id,
            'trans_amount' => (int) $total,
            'return_url' => config('app.fe_url') . '/quan-ly-ma-kich-hoat/',
        ]);
        return response()->json([
            'redirect_to' => $redirect
        ]);
    }
    
    private function handlePaymentVnpay(PaymentHistory $paymentHistory, int $total): JsonResponse
    {
        $payment = app(VnpayService::class);
        $data = ['total' => $total, 'code' => $paymentHistory->id];
        $link = $payment->createRedirectLinkSeri($data);
        return response()->json([
            'redirect_to' => $link
        ]);
    }
}