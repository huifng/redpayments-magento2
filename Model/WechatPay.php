<?php

namespace Redpayments\Magento2\Model;

/**
 * Class WechatPay
 * @package Redpayments\Magento2\Model
 * @description
 * @version 1.0.0
 */
class WechatPay extends AbstractPayment
{
    const CODE = 'redpayments_wechatpay';

    protected $_code = self::CODE;

}
