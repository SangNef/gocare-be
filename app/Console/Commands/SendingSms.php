<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Pingback;
use App\Models\Pingback_log;
use App\Models\UserApi;
use App\Models\Vinatopup;
use App\Services\EsmsService;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Smssent;

class SendingSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
//0369433519
//0976715007
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $smses = Smssent::where('status', 'Chờ gửi')
            ->whereBetween('created_at', [
                Carbon::now()->subMinute(),
                Carbon::now()->now(),
            ])
            ->get();

        /** @var EsmsService $esms */
        $esms = app(EsmsService::class);
        foreach ($smses as $sms) {
            if (strlen($sms->message) > 50) {
                $result = $esms->sendSms($sms->phone, $sms->message, $sms->id);
                $sms->result = $result;
                $sms->status = 'Đã gửi yêu cầu';
                $sms->save();
                $data = json_decode($result, true);
                if (@$data['CodeResult'] != 100) {
                    $sms->status = 'Không gửi được yêu cầu';
                }
            } else {
                $sms->result = 'Do dai khong hop le';
                $sms->status = 'Thất bại';
            }
            
            $sms->save();
        }


    }
}
