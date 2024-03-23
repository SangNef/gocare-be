<?php


namespace App\Services\Payments;

use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class OnepayService implements PaymentInterface
{
    protected $url;
    protected $merchantID;
    protected $hashKey;
    protected $accessCode;
    protected $status = [];
    protected $errors = [
        1 => 'Ngân hàng phát hành thẻ không cấp phép cho giao dịch hoặc thẻ chưa được kích hoạt dịch vụ thanh toán trên Internet. Vui lòng liên hệ ngân hàng theo số điện thoại sau mặt thẻ được hỗ trợ chi tiết.',
        2 => 'Ngân hàng phát hành thẻ từ chối cấp phép cho giao dịch. Vui lòng liên hệ ngân hàng theo số điện thoại sau mặt thẻ để biết chính xác nguyên nhân Ngân hàng từ chối.',
        3 => 'Cổng thanh toán không nhận được kết quả trả về từ ngân hàng phát hành thẻ. Vui lòng liên hệ với ngân hàng theo số điện thoại sau mặt thẻ để biết chính xác trạng thái giao dịch và thực hiện thanh toán lại',
        4 => 'Giao dịch không thành công do thẻ hết hạn sử dụng hoặc nhập sai thông tin tháng/ năm hết hạn của thẻ. Vui lòng kiểm tra lại thông tin và thanh toán lại',
        5 => 'Thẻ không đủ hạn mức hoặc tài khoản không đủ số dư để thanh toán. Vui lòng kiểm tra lại thông tin và thanh toán lại',
        6 => 'Quá trình xử lý giao dịch phát sinh lỗi từ ngân hàng phát hành thẻ. Vui lòng liên hệ ngân hàng theo số điện thoại sau mặt thẻ được hỗ trợ chi tiết.',
        7 => 'Đã có lỗi phát sinh trong quá trình xử lý giao dịch. Vui lòng thực hiện thanh toán lại.',
        8 => 'Số thẻ không đúng. Vui lòng kiểm tra và thực hiện thanh toán lại',
        9 => 'Tên chủ thẻ không đúng. Vui lòng kiểm tra và thực hiện thanh toán lại',
        10 => 'Thẻ hết hạn/Thẻ bị khóa. Vui lòng kiểm tra và thực hiện thanh toán lại',
        11 => 'Thẻ chưa đăng ký sử dụng dịch vụ thanh toán trên Internet. Vui lòng liên hê ngân hàng theo số điện thoại sau mặt thẻ để được hỗ trợ.',
        12 => 'Ngày phát hành/Hết hạn không đúng. Vui lòng kiểm tra và thực hiện thanh toán lại',
        13 => 'Thẻ/ tài khoản đã vượt quá hạn mức thanh toán. Vui lòng kiểm tra và thực hiện thanh toán lại',
        21 => 'Số tiền không đủ để thanh toán. Vui lòng kiểm tra và thực hiện thanh toán lại',
        22 => 'Thông tin tài khoản không đúng. Vui lòng kiểm tra và thực hiện thanh toán lại',
        23 => 'Tài khoản bị khóa. Vui lòng liên hê ngân hàng theo số điện thoại sau mặt thẻ để được hỗ trợ',
        24 => 'Thông tin thẻ không đúng. Vui lòng kiểm tra và thực hiện thanh toán lại',
        25 => 'OTP không đúng. Vui lòng kiểm tra và thực hiện thanh toán lại',
        253 => 'Quá thời gian thanh toán. Vui lòng thực hiện thanh toán lại',
        99 => 'Người sử dụng hủy giao dịch',
        "B" => 'Giao dịch không thành công do không xác thực được 3D-Secure. Vui lòng liên hệ ngân hàng theo số điện thoại sau mặt thẻ được hỗ trợ chi tiết.',
        "E" => 'Giao dịch không thành công do nhập sai CSC (Card Security Card) hoặc ngân hàng từ chối cấp phép cho giao dịch. Vui lòng liên hệ ngân hàng theo số điện thoại sau mặt thẻ được hỗ trợ chi tiết.',
        "F" => 'Giao dịch không thành công do không xác thực được 3D-Secure. Vui lòng liên hệ ngân hàng theo số điện thoại sau mặt thẻ được hỗ trợ chi tiết.',
        "Z" => 'Giao dịch bị từ chối. Vui lòng liên hệ OnePAY để được hỗ trợ (Email: support@onepay.vn / Hotline: 1900 633 927).'
    ];


    CONST STATUS_1 = 'Chờ thanh toán';
    CONST STATUS_2 = 'Thanh toán thất bại';
    CONST STATUS_3 = 'Thanh toán thành công';
    CONST VERSION = 2;

    public function __construct()
    {
        $this->url = config('payment.providers.onepay.url');
        $this->merchantID = config('payment.providers.onepay.merchant_id');
        $this->hashKey = config('payment.providers.onepay.hash_key');
        $this->accessCode = config('payment.providers.onepay.access_code');
        $this->status = [
            1 => self::STATUS_1,
            2 => self::STATUS_2,
            3 => self::STATUS_3,
        ];
    }

    public function loadInstallmentProfile()
    {
        $this->merchantID = config('payment.providers.onepay.installment_merchant_id');
        $this->hashKey = config('payment.providers.onepay.installment_hash_key');
        $this->accessCode = config('payment.providers.onepay.installment_access_code');
    }

    public function createRedirectLinkForOrder(Order $order, $installment = false)
    {
        if ($installment) {
            $this->loadInstallmentProfile();
        }

        return $this->createRedirectLink([
            'vpc_ReturnURL' => config('app.fe_url') . '/don-hang/processing/' . $order->id,
            'vpc_MerchTxnRef' => $order->code . uniqid(),
            'vpc_OrderInfo' => $order->code,
            'vpc_TicketNo' => request()->ip(),
            'vpc_Amount' => (int) $order->total * 100,
            'AgainLink' => config('app.fe_url') . '/checkout',
            'Title' => 'Thanh toan don hang AZPro',
        ]);
    }

    public function createRedirectLink($params)
    {
        $params = array_merge([
            'vpc_Version' => 2,
            'vpc_Currency' => 'VND',
            'vpc_Command' => 'pay',
            'vpc_AccessCode' => $this->accessCode,
            'vpc_Merchant' => $this->merchantID,
            'vpc_Locale' => 'vn',
        ], $params);
        $params['vpc_SecureHash'] = $this->generateHashedString($params);

        return $this->url . '?' . http_build_query($params);
    }

    protected function generateHashedString($params)
    {
        $keys = array_filter(array_keys($params), function($key) {
            return strpos($key, 'vpc_') === 0 || strpos($key, 'user_') === 0;
        });
        asort($keys);
        $keys = array_flip($keys);
        array_walk($keys, function(&$v, $key) use($params) {
            return $v = implode('=', [$key, $params[$key]]);
        });

        return strtoupper(hash_hmac('SHA256', implode('&', $keys), pack('H*',$this->hashKey)));
    }

    public function verifyPayment($params)
    {
        if (isset($params['vpc_ItaBank']))
        {
            $this->loadInstallmentProfile();
        }
        if (isset($params['vpc_TxnResponseCode']) && @$params['vpc_SecureHash'] !== $this->generateHashedString(array_except($params, ['vpc_SecureHash']))) {
            if (isset($this->errors[$params['vpc_TxnResponseCode']])) {
                return [
                    'status' => 0,
                    'mess' => $this->errors[$params['vpc_TxnResponseCode']],
                ];
            }
            if ((int) $params['vpc_TxnResponseCode'] === 0){
                return [
                    'status' => 1,
                    'amount' => $params['vpc_Amount']/100,
                    'order_code' => $params['vpc_OrderInfo'],
                    'trans_id' => $params['vpc_MerchTxnRef']
                ];
            }
        }
        \Log::error($params);

        return [
            'status' => 0,
            'mess' => 'Có lỗi xảy ra. Vui lòng thử lại sau hoặc liên hệ admin!',
        ];
    }

}