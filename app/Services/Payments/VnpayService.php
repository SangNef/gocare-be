<?php

namespace App\Services\Payments;

use App\Models\Order;

class VnpayService
{
    protected $url;
    protected $vnp_TmnCode;
    protected $vnp_HashSecret;
    protected $version;
    protected $startTime;
    protected $expire;

    public function __construct()
    {
        $this->url = config('payment.providers.vnpay.url');
        $this->vnp_TmnCode = config('payment.providers.vnpay.vnp_TmnCode');
        $this->vnp_HashSecret = config('payment.providers.vnpay.vnp_HashSecret');
        $this->version = config('payment.providers.vnpay.version');
        $this->startTime = date("YmdHis");
        $this->expire = date('YmdHis', strtotime('+15 minutes', strtotime($this->startTime)));
    }

    public function createRedirectLinkOrder(Order $order, $txnRef = null)
    {
        if ($txnRef == null) $txnRef = $order->id;
        $params = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => (int)$order->total * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => 'vn',
            "vnp_OrderInfo" => "Thanh toan GD: mua hàng tại GOCARE",
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => config('app.fe_url') . '/dat-hang-thanh-cong/' . $order->access_key,
            "vnp_TxnRef" => $txnRef,
            "vnp_ExpireDate" => $this->expire
        );

        return $this->createRedirectLink($params);
    }

    public function createRedirectLinkSeri($data)
    {
        $params = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => (int)$data['total'] * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => 'vn',
            "vnp_OrderInfo" => "Thanh toan kich hoat ma series",
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => config('app.fe_url') . '/quan-ly-ma-kich-hoat/',
            "vnp_TxnRef" => $data['code'],
            "vnp_ExpireDate" => $this->expire
        );

        return $this->createRedirectLink($params);
    }

    public function createRedirectLink($params)
    {
        $params['vnp_SecureHash'] = $this->generateHashedString($params);
        return $this->url . '?' . http_build_query($params);
    }

    protected function generateHashedString($params)
    {
        ksort($params);
        $i = 0;
        $hashdata = "";
        foreach ($params as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        return hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);

    }

    public function verifyPayment($params)
    {
        $SecureHash = explode("?", $params['vnp_SecureHash']);
        $vnp_SecureHash = $SecureHash[0];
        $inputData = array();
        foreach ($params as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHash']);
        $secureHash = $this->generateHashedString($inputData);
        if ($secureHash == $vnp_SecureHash) {
            return [
                'status' => 1,
                'amount' => $params['vnp_Amount'] / 100,
                'order_code' => $params['vnp_TxnRef'],
                'trans_id' => $params['vnp_TransactionNo']
            ];
        } else {
            return [
                'status' => 0,
                'mess' => 'Chu ky khong hop le',
            ];
        }
        \Log::error($params);

        return [
            'status' => 0,
            'mess' => 'Có lỗi xảy ra. Vui lòng thử lại sau hoặc liên hệ admin!',
        ];
    }

}