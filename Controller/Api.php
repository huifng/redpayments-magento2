<?php

namespace Redpayments\Magento2\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Redpayments\Magento2\Helper\General as RedpayPayHelper;
use Redpayments\Magento2\Model\WechatPay;
use Redpayments\Magento2\Payment\Cryptography;
use Redpayments\Magento2\Payment\Notify;

abstract class Api extends Controller
{
    /**
     * Notify constructor.
     * @param Context $context
     * @param PaymentHelper $paymentHelper
     * @param RedpayPayHelper $redpayPayHelper
     */
    public function __construct(
        Context $context,
        WechatPay $payment,
        PaymentHelper $paymentHelper,
        RedpayPayHelper $redpayPayHelper
    )
    {
        $this->resultFactory = $context->getResultFactory();
        $this->paymentHelper = $paymentHelper;
        $this->redpayPayHelper = $redpayPayHelper;
        $this->paymentInstance = $payment;
        parent::__construct($context);
    }

    protected function getOrderInfo($signVerificationNeeded)
    {
        $notify = new Notify(
            new Cryptography(
                $this->redpayPayHelper->getApiKey()
            )
        );
        return $notify->getOrderInfo($signVerificationNeeded);
    }
}