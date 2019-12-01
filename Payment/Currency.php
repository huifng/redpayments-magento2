<?php

namespace Redpayments\Magento2\Payment;

class Currency
{
    const AUD = 'AUD';

    const CNY = 'CNY';

    const CURRENCY_EXCHANGE = 'CURRENCY_EXCHANGE';

    public static function isSupported($currency)
    {
        $currency = \strtoupper(\trim($currency));
        return $currency === self::AUD || $currency === self::CNY;
    }

    public static function calculateToAud($amount, $exchange_rate)
    {
        return \strval(\round($amount / $exchange_rate, 2));
    }
}
