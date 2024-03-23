<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 8/17/19
 * Time: 1:27 AM
 */

namespace App\Notifications;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Telegram
{
    protected $token;

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getClient()
    {
        $token = $this->token ?: config('services.telegram.token');
        return new Client([
            'base_uri' => "https://api.telegram.org/bot{$token}/",
        ]);
    }

    protected function send($data)
    {
        try {
            $client = $this->getClient();

            $client->post('sendMessage', [
                'json' => $data
            ]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
        }
    }

    public function sendText($mess, $group = 0)
    {
        $this->send([
            'chat_id' => $group ?: config('services.telegram.group_id'),
            'text' => $mess,
        ]);
    }
}
