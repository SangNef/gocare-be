<?php 
namespace App\Services\Payments;

use App\Models\Order;

interface PaymentInterface {
    public function createRedirectLink($params);
    public function createRedirectLinkForOrder(Order $order, $installment = false);
    public function verifyPayment($params);
}