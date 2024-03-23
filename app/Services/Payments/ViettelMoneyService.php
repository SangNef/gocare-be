<?php


namespace App\Services\Payments;


use GuzzleHttp\Client;

class ViettelMoneyService
{
    protected $url;

    protected $version;

    protected $merchantCode;

    protected $accessCode;

    protected $key;

    public function __construct()
    {
        $this->url = config('payment.providers.viettelmoney.url');
        $this->version = config('payment.providers.viettelmoney.version');
        $this->merchantCode = config('payment.providers.viettelmoney.merchant_code');
        $this->accessCode = config('payment.providers.viettelmoney.access_code');
        $this->key = config('payment.providers.viettelmoney.key');
    }

    public function createRedirectLinkForOrder($data) {
        $data = array_merge([
            'command' => 'PAYMENT',
            'version' => $this->version,
            'merchant_code' => $this->merchantCode,
        ], $data);
        $data['billcode'] = $data['order_id'];

        $data['check_sum'] = $this->generateChecksum(array_only($data, [
            'billcode',
            'command',
            'merchant_code',
            'order_id',
            'trans_amount',
            'version'
        ]));

        return $this->createRedirectLink($data);
    }

    public function createRedirectLink($params)
    {
        return $this->url . '?' . http_build_query($params);
    }

    public function createQrCode($data)
    {
        $data = array_merge([
            'command' => 'PAYMENT',
            'version' => $this->version,
            'merchant_code' => $this->merchantCode,
        ], $data);
        $data['billcode'] = $data['order_id'];

        $data['check_sum'] = $this->generateChecksum(array_only($data, [
            'billcode',
            'command',
            'merchant_code',
            'order_id',
            'trans_amount',
            'version'
        ]));

        $client = new Client();
        return $client->post($this->url . '/genQRCodePayment', [
            'headers' => [
                'content-type : application/x-www-form-urlencoded'
            ],
            'form_params' => $data
        ])->getBody()->getContents();
    }

    public function verifyChecksum($checksum, $data)
    {
        $data = array_merge([
            'merchant_code' => $this->merchantCode,
        ], $data);
        $data['billcode'] = $data['order_id'];

        return urlencode($checksum) == $this->generateChecksum($data);
    }

    public function generateChecksum($data)
    {
        ksort($data);
        $string = $this->accessCode.implode('', $data);
        $hash = hash_hmac("sha1", $string, $this->key, true);

        return urlencode(utf8_encode(base64_encode($hash)));
    }

}
