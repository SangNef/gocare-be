<?php

namespace App\Listeners;

use App\Events\WarrantyOrderSaved;
use App\Repositories\WarrantyOrderRepository;
use App\Models\WarrantyOrder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class UpdateStatusWarrantyOrder
{
    protected $wOrderRp;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(WarrantyOrderRepository $wOrderRp)
    {
        $this->wOrderRp = $wOrderRp;
    }

    /**
     * Handle the event.
     *
     * @param  WarrantyOrderSaved  $event
     * @return void
     */
    public function handle(WarrantyOrderSaved $event)
    {
        $wOrder = $event->getWarrantyOrder();
        $data = [
            'status' => WarrantyOrder::STATUS_RECEIVED
        ];
        $wopsProcess = $this->wOrderRp->getProcessSeries($wOrder);
        $totalProcessing = $wopsProcess->processing + $wopsProcess->processed + $wopsProcess->advance_warranty;

        if ($totalProcessing) {
            $data['status'] = WarrantyOrder::STATUS_PROCESSING;
        }
        if ($wopsProcess->returned == $wopsProcess->total) {
            $data['status'] = WarrantyOrder::STATUS_SUCCESS;
            $data['returned_at'] = Carbon::now();
        }

        $wOrder->update($data);

        $cmd = 'php ' . base_path() . '/artisan notification:warranty-order ' . $wOrder->id;
        shell_exec($cmd . ' > /dev/null 2>&1 &');
    }


}
