<?php

namespace Redpayments\Magento2\Payment;

use function number_format;

class Order
{
    public static function calculateAmount($amount, $fee)
    {
        if($amount <= 0){
            return 0;
        }
        if($fee <= 0){
            return $amount;
        }
        $amount += $amount * ($fee / 100);
        return number_format($amount, 2);
    }
}