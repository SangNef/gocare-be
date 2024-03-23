<?php

namespace App\Repositories;

use App\Models\CODOrder;
use App\Models\CODOrdersShipping;
use App\Models\Order;

class CODOrderShippingRepository
{
    public function prepareOrder($partner, $ids = [])
    {
        return CODOrder::query()
            ->where(function ($query) use ($partner, $ids) {
                if (!empty($ids)) {
                    $query->whereIn('id', $ids);
                }
                $query->where('partner', $partner);
            })
            ->get();
    }

    public function create(CODOrdersShipping $sOrder, $cOrderIds = [], $billData = [])
    {
        CODOrder::whereIn('id', $cOrderIds)->update(['so_id' => $sOrder->id]);
        if (in_array($sOrder->status, [2, 3])) {
            switch ($sOrder->type) {
                case CODOrdersShipping::TYPE_EXPORT:
                    return $this->processTypeExport($cOrderIds);
                    break;
                case CODOrdersShipping::TYPE_REFUND:
                    return $this->processTypeRefund($cOrderIds);
                    break;
                default:
                    return;
            }
        }
        if (!empty($billData)) {
            foreach($cOrderIds as $corderId) {
                $codAmount = @$billData[$corderId]['cod_amount'];
                $feeAmount = @$billData[$corderId]['fee_amount'];
                $corder = CODOrder::find($corderId);
                if ($codAmount && $feeAmount && $corder) {
                    $corder->update([
                        'cod_amount' => $codAmount,
                        'fee_amount' => $feeAmount,
                        'real_amount' => $feeAmount
                    ]);
                }
            }
        }
    }
    

    public function update(CODOrdersShipping $sOrder, $partnerIds, $billData = [])
    {
        $this->removeOrder($sOrder->id, $partnerIds);
        return $this->create($sOrder, $partnerIds, $billData);
    }

    protected function processTypeExport($cOrderIds)
    {
        return Order::query()
            ->whereHas('codOrder', function ($query) use ($cOrderIds) {
                $query->whereIn('id', $cOrderIds);
            })
            ->get()
            ->map(function ($order) {
                if ($order->self_cod_service) {
                    $order->approve = 1;
                    $order->status = 2;
                    $order->codOrder->update([
                        'compare_status' => 1
                    ]);
                }
                $order->shipping_status = 'Thành công';
                $order->save();
            });
    }

    protected function processTypeRefund($cOrderIds)
    {
        return Order::query()
            ->whereHas('codOrder', function ($query) use ($cOrderIds) {
                $query->whereIn('id', $cOrderIds);
            })
            ->each(function ($order) {
                $order->status = 3;
                $order->shipping_status = 'Hoàn';
                app(\App\Repositories\CustomerBacklogRepository::class)->processForUpdateOrder($order);
            });
    }

    public function removeOrder($soId, $exclude = [])
    {
        return CODOrder::query()
            ->where('so_id', $soId)
            ->whereNotIn('id', $exclude)
            ->orderBy('id', 'desc')
            ->update([
                'so_id' => NULL
            ]);
    }
}
