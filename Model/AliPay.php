<?php

namespace Redpayments\Magento2\Model;

/**
 * Class AliPay
 * @package Redpayments\Magento2\Model
 * @description
 * @version 1.0.0
 */
class AliPay extends AbstractPayment
{
    const CODE = 'redpayments_alipay';

    protected $_code = self::CODE;
}
