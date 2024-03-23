<?php

/**
 * Controller genrated using LaraAdmin
 * Help: http://laraadmin.com
 */

namespace App\Http\Controllers\LA;

use App\Http\Controllers\Controller;
use App\Services\QrCodeService;

class QRCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function showQrCodeForOrder($id)
    {
        $code = $this->qrCodeService->generateQrCode((string) url('/admin/orders/' . $id));
        echo $code;
    }

    public function showQrCodeForShippingOrder($id)
    {
        $code = $this->qrCodeService->generateQrCode((string) url('/admin/shipping-orders/' . $id . '/edit'));
        echo $code;
    }


    public function showQrCodeForWarrantyOrder($id)
    {
        $code = $this->qrCodeService->generateQrCode((string) url('/admin/warrantyorders/' . $id . '/edit'));
        echo $code;
    }
}
