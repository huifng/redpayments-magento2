<?php

namespace Redpayments\Magento2\Payment;

class Response
{
    const OK = 'success';

    const BAD = 'failed';

    public static function ajaxReturnSuccess($content = 'success')
    {
        echo $content;
        exit;
    }

    public static function ajaxReturnFailure($content = 'failed')
    {
        echo $content;
        exit;
    }
}