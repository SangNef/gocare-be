<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    public function generateQrCode($url, $toBase64 = false)
    {
        $qrCode = new QrCode($url);
        $qrCode->setSize(100);
        $qrCode->setMargin(0);
        if (!$toBase64) {
            header('Content-Type: ' . $qrCode->getContentType());
        }
        return $qrCode->writeString();
    }
}
