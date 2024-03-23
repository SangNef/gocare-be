<?php
namespace App\Observes;

use App\Models\ImportOrder;
use App\Models\ImportProduct;
use App\Models\IOSeri;
use App\Models\Order;
use App\Models\Product;
use App\Models\Produce;
use App\Models\ProductSeri;
use App\Models\StoreProductGroupAttributeExtra;
use App\Models\StoreProduct;
use App\Models\ProductGroupAttributeMedia;
use App\Repositories\OrderRepository;
use App\Repositories\ProductSeriesRepository;

class ImportProductObserve
{
    public function updating(ImportProduct $importProduct)
    {
        if ($importProduct->isDirty('done'))
        {
            $changed = $importProduct->done - $importProduct->getOriginal('done');
            if ($changed > 0) {
                $orderRp = app(OrderRepository::class);
                $order = $orderRp->createImportOrder($importProduct, $changed);
                ImportOrder::create([
                    'import_id' => $importProduct->import_id,
                    'order_id' => $order->id,
                    'ip_id' => $importProduct->id,
                    'product_id' => $importProduct->product_id,
                ]);
            }

        }
        if ($importProduct->isDirty('quantity'))
        {
            $product = $importProduct->product;
            $changed = $importProduct->quantity - $importProduct->getOriginal('quantity');
            if ($changed > 0 && $product->isUseSeries()) {
                $pSeriRp = app(ProductSeriesRepository::class);
                $attrIds = $importProduct->attrs_value ? implode(',', $importProduct->attrs_value) : '';
                $groupAttr = null;
                if ($attrIds) {
                    $attrsValue = ProductGroupAttributeMedia::where('attribute_value_ids', $attrIds)
                        ->where('product_id', $product->id)
                        ->first();
                    $groupAttr = $attrsValue ? $attrsValue->id : null;
                }
                $newSeris = $pSeriRp->createSeries($product->id, $changed, [
                    'qr_code_status' => 2,
                    'store_id' => $importProduct->import->store_id,
                    'group_attribute_id' => $groupAttr,
                ]);
                $importProduct->pseri_ids .= ',' . implode(',', $newSeris);
            } else if ($changed < 0 && $product->isUseSeries()) {
                $existedSeris = explode(',', $importProduct->pseri_ids);
                $deletingIds = array_splice($existedSeris, 0, $changed, []);
                ProductSeri::whereIn('id', $deletingIds)
                    ->delete();
                $importProduct->pseri_ids = implode(',', $existedSeris);
            }
        }

    }

    public function creating(ImportProduct $importProduct)
    {
        $changed = $importProduct->quantity;
        $product = $importProduct->product;
        $pSeriRp = app(ProductSeriesRepository::class);
        $attrIds = $importProduct->attrs_value ? implode(',', $importProduct->attrs_value) : '';
        $groupAttr = null;
        if ($attrIds) {
            $attrsValue = ProductGroupAttributeMedia::where('attribute_value_ids', $attrIds)
                ->where('product_id', $product->id)
                ->first();
            $groupAttr = $attrsValue ? $attrsValue->id : null;
        }
        $newSeris = $pSeriRp->createSeries($product->id, $changed, [
            'qr_code_status' => 2,
            'store_id' => $importProduct->import->store_id,
            'group_attribute_id' => $groupAttr,
        ]);
        $importProduct->pseri_ids = implode(',', $newSeris);
    }

    public function deleted(ImportProduct $importProduct)
    {
        $importOrders = ImportOrder::where('import_id', $importProduct->import->id)
            ->where('ip_id', $importProduct->id)
            ->get();
        foreach ($importOrders as $importOrder) {
            $order = Order::find($importOrder->order_id);
            $order->status = 4;
            $order->save();
        }
        if ($importProduct->pseri_ids) {
            ProductSeri::whereIn('id', explode(',', $importProduct->pseri_ids))
                ->delete();
        }
    }
}
