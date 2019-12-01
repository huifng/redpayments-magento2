<?php

namespace Redpayments\Magento2\Controller\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\View\Result\PageFactory;
use Magento\Payment\Helper\Data as PaymentHelper;
use Redpayments\Magento2\Controller\Controller;
use Redpayments\Magento2\Helper\General as RedpayPayHelper;
use Redpayments\Magento2\Logger\Logger;
use Redpayments\Magento2\Payment\Utils;

class Redirect extends Controller
{
    /**
     * Redirect constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param PageFactory $resultPageFactory
     * @param PaymentHelper $paymentHelper
     * @param RedpayPayHelper $redpayPayHelper
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        PaymentHelper $paymentHelper,
        RedpayPayHelper $redpayPayHelper,
        Logger $logger
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->paymentHelper = $paymentHelper;
        $this->redpayPayHelper = $redpayPayHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute Redirect to Mollie after placing order
     */
    public function execute()
    {
        try{
            $order = $this->checkoutSession->getLastRealOrder();
            if(!$order){
                $msg = __('Order not found.');
                $this->redpayPayHelper->addTolog('error', $msg);
                $this->_redirect('checkout/cart');
                return;
            }
            $payment = $order->getPayment();
            if(!isset($payment) || empty($payment)){
                $this->redpayPayHelper->addTolog('error', 'Order Payment is empty');
                $this->_redirect('checkout/cart');
                return;
            }
            $method = $order->getPayment()->getMethod();
            $methodInstance = $this->paymentHelper->getMethodInstance($method);
            if($methodInstance instanceof \Redpayments\Magento2\Model\AbstractPayment){
                $redirectUrl = $methodInstance->startTransaction($order);
                /**
                 * @var Http $response
                 */
                if('redpayments_alipay' === $method){
                    echo $redirectUrl;
                }else if('redpayments_wechatpay' === $method){
                    if(Utils::isMobile()){
                        if(Utils::isWeixin() === false){
                            die('Please open the page within Wechat');
                        }
                        $response = $this->getResponse();
                        $response->setRedirect($redirectUrl);
                    }else{
                        $response = $this->getResponse();
                        $response->setRedirect($redirectUrl);
                    }
                }else{
                    echo 'Unsupported payment method';
                }
            }else{
                $msg = __('Paymentmethod not found.');
                $this->messageManager->addErrorMessage($msg);
                $this->redpayPayHelper->addTolog('error', $msg);
                $this->checkoutSession->restoreQuote();
                $this->_redirect('checkout/cart');
            }
        }catch(\Exception $e){
            $this->messageManager->addExceptionMessage(
                $e, __($e->getMessage())
            );
            $this->redpayPayHelper->addTolog('error', $e->getMessage());
            $this->checkoutSession->restoreQuote();
            $this->_redirect('checkout/cart');
        }
    }
}
