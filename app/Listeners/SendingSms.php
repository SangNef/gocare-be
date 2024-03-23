<?php

namespace App\Listeners;

use App\Events\WarrantyActivated;
use App\Models\ProductSeri;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class SendingSms
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WarrantyActivated  $event
     * @return void
     */
    public function handle(WarrantyActivated $event)
    {
        /** @var ProductSeri $seri */
        $seri = $event->getSeri();
        DB::table('smssents')
            ->insert([
                'mod' => 'Kích hoạt bảo hành',
                'username' => $seri->name,
                'phone' => $seri->phone,
                'message' => 'Kich hoat thanh cong. Ma Seri:' . $seri->seri_number
                    . '. Thoi gian tu:' . $seri->activated_at->format('d/m/Y') .' den: ' . $seri->expired_at->format('d/m/Y')
                    . '. Cam on quy khach da tin tuong va su dung.',
                'status' => 'Chờ gửi',
                'created_at' => Carbon::now()
            ]);
    }
}
