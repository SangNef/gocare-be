<?php

namespace App\Exceptions;

class CODException extends \Exception
{
    public static function createBilladingFail()
    {
        return new static('Đẩy đơn không thành công');
    }
}
