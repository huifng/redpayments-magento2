<?php

namespace Redpayments\Magento2\Controller;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Payment\Helper\Data;
use Redpayments\Magento2\Logger\Logger;

abstract class Controller extends Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Data
     */
    protected $paymentHelper;
    /**
     * @var \Redpayments\Magento2\Payment\Helper\General
     */
    protected $redpayPayHelper;
    /**
     * @var \Redpayments\Magento2\Payment\Model\AbstractPayment
     */
    protected $paymentInstance;
    /**
     * @var Logger $logger
     */
    protected $logger;
}