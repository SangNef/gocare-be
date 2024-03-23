<?php

namespace App\Repositories;

use App\Models\WarrantyOrder;
use App\Models\WarrantyOrderProductSeri;
use Carbon\Carbon;

class WarrantyOrderProductSeriRepository
{
    public function create(WarrantyOrder $warrantyOrder, $series)
    {
        $series = collect($series);
        foreach ($warrantyOrder->warrantyOrderProducts as $wop) {
            $inputs = $series->whereLoose('product_id', $wop->product_id);
            if (!empty($inputs)) {
                foreach ($inputs as $input) {
                    $returnAt = null;
                    if (@$input['return_at']) {
                        $returnAt = strpos(@$input['return_at'], '/') !== false 
                            ? Carbon::createFromFormat('d/m/Y H:i:s', $input['return_at'])->format('Y-m-d H:i:s') 
                            : @$input['return_at'];
                    }
                    if ((!isset($input['seri_id']) && !isset($input['id'])) || !isset($input['id'])) {
                        WarrantyOrderProductSeri::create([
                            'warranty_order_product_id' => $wop->id,
                            'product_seri_id' => @$input['seri_id'],
                            'status' => (int) $input['status'],
                            'note' => $input['note'],
                            'error_type' => $input['error_type'],
                            'return_at' => $returnAt
                        ]);
                    } else {
                        WarrantyOrderProductSeri::query()
                            ->where('id', $input['id'])
                            ->update([
                                'warranty_order_product_id' => $wop->id,
                                'product_seri_id' => @$input['seri_id'],
                                'status' => (int) $input['status'],
                                'note' => $input['note'],
                                'error_type' => $input['error_type'],
                                'return_at' => $returnAt
                            ]);
                    }
                }
            }
        }
    }

    public function update(WarrantyOrder $warrantyOrder, $series)
    {
        $warrantyOrder->warrantyOrderProductSeries()
            ->whereNotIn('warranty_order_product_series.id', array_column($series, 'id'))
            ->delete();
        return $this->create($warrantyOrder, $series);
    }
}
