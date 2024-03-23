<?php

namespace App\Enums;

class PaymentsEnum
{
    const PAYMENT_VIETTEL = 'Viettel';
    const PAYMENT_VNPAY = 'Vnpay';
    
    const PAYMENT_SOURCES = [
        self::PAYMENT_VIETTEL,
        self::PAYMENT_VNPAY,
    ];
}