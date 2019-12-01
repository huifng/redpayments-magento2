<?php

namespace Redpayments\Magento2\Payment;

class Payment
{
    const LOG_TABLE_NAME = 'redpayments_log';

    const METHOD_WECHATPAY_ID = 'redpayments_wechatpay';

    const METHOD_ALIPAY_ID = 'redpayments_alipay';

    const PAYMENT_WECHAT = 'wechatpay';

    const PAYMENT_ALIPAY = 'alipay';

    const WECHAT = 'WechatPay';

    const ALIPAY = 'Alipay';

    const PAY_NEW = 'new';

    const PAY_PENDING = 'pending';

    const PAY_PROCESSING = 'processing';

    const PAY_PAID = 'paid';

    const PAY_CANCELLED = 'cancelled';

    const PAY_FAILED = 'failed';

    const PAY_REFUNDED = 'refunded';

    const PAY_EXPIRED = 'expired';

    const PAY_COMPLETED = 'completed';

    const META_TRADE_ID = 'redpayments_trade_id';

    const META_TRANSACTION_ID = 'redpayments_transaction_id';
    /**
     * @var string
     */
    private $type;

    private static $methods = array(
        self::METHOD_ALIPAY_ID,
        self::METHOD_WECHATPAY_ID
    );

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public static function getMethods()
    {
        return self::$methods;
    }

    /**
     * @param array $methods
     */
    public static function setMethods($methods)
    {
        self::$methods = $methods;
    }
}
