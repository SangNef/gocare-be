<?php


namespace App\Services;


use Carbon\Carbon;
use GuzzleHttp\Client;

class ViettelActivationService
{
    protected $partnerCode;

    protected $secret;

    protected $url;

    protected $tenantCode;

    public function __construct()
    {
        $this->url = config('services.activation.url');
        $this->partnerCode = config('services.activation.partner_code');
        $this->secret = config('services.activation.secret');
        $this->tenantCode = config('services.activation.tenant_code');
    }

    public function activate($activationCode = [])
    {
        if ($token = $this->login())
        {
            $client = new Client();

            $response = $client->post($this->url . '/account/partner/code/activate/multiple', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'codes' => array_map(function ($code) {
                        return [
                            'code' => $code,
                            'purchasedDate' => Carbon::now()->format('Y-m-d H:i:s'),
                            'codeStatus' => 'PURCHASED'
                        ];
                    }, $activationCode)
                ])
            ])->getBody()->getContents();
            \Log::error($response);
            return json_decode($response, true);
        }
    }

    protected function login()
    {
        $client = new Client();
        $response = $client->post($this->url . '/uaa/partner/token', [
            'headers' => [
                'X-TENANT' => $this->tenantCode,
            ],
            'form_params' => [
                'id' => $this->partnerCode,
                'secret' => $this->secret
            ]
        ]);
        $bodyContents = $response->getBody()->getContents();

        if ($response->getStatusCode() == 200) {
            $result = json_decode($bodyContents, true);
            return $result['token'];
        }
    }

}