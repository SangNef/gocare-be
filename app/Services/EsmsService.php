<?php


namespace App\Services;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class EsmsService
{
    protected $url;
    protected $apiKey;
    protected $secret;
    protected $status = [];

    CONST STATUS_1 = 'Chờ duyệt';
    CONST STATUS_2 = 'Chờ gửi';
    CONST STATUS_3 = 'Đang gửi';
    CONST STATUS_4 = 'Bị từ chối';
    CONST STATUS_5 = 'Đã gửi xong';
    CONST STATUS_6 = 'Đã bị xoá';

    public function __construct()
    {
        $this->url = config('services.esms.url');
        $this->apiKey = config('services.esms.api_key');
        $this->secret = config('services.esms.api_secret');
        $this->status = [
            1 => self::STATUS_1,
            2 => self::STATUS_2,
            3 => self::STATUS_3,
            4 => self::STATUS_4,
            5 => self::STATUS_5,
            6 => self::STATUS_6,
        ];
    }

    public function getStatusLabel($status)
    {
        return @$this->status[$status];
    }

    protected function prepareData(array $data)
    {
        return array_merge($data, [
            'ApiKey' => $this->apiKey,
            'SecretKey' => $this->secret,
            'IsUnicode' => 0,
        ]);
    }

    public function sendSms($receiver, $content, $requestId)
    {
        try {
            $client = new Client([
                'base_uri' => $this->url,
                'headers' => ['content-type' => 'application/json']
            ]);
            return $client->post('SendMultipleMessage_V4_post_json', [
                'body' => json_encode($this->prepareData([
                    'Content' => $content,
                    'Phone' => $receiver,
                    'RequestId' => $requestId,
                    'CallbackUrl' => 'http://dt-esms-callback.azpro.vn/esms-callback',
                    'Brandname' => 'AZPro',
                    'SmsType' => '2'
                ]))
            ])->getBody()->getContents();
        } catch (ClientException $clientException) {
            return $clientException->getResponse()->getBody()->getContents();
        } catch (\Exception $exception) {
            \Log::error($exception->getMessage());
            \Log::error($exception->getTraceAsString());
            return $exception->getMessage();
        }
    }

}