<?php

namespace Redpayments\Magento2\Controller\Checkout;

use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Redpayments\Magento2\Controller\Api;
use Redpayments\Magento2\Payment\Response;

class Notify extends Api
{
    /**
     * Execute Redirect to Mollie after placing order
     */
    public function execute()
    {
        /**
         * @var Raw $result
         */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHeader('content-type', 'text/plain');
        try{
            $orderInfo = $this->getOrderInfo(true);
            //$this->redpayPayHelper->addTolog('info', 'orderInfo:' . var_export($orderInfo));
            if($orderInfo){
                $this->paymentInstance->processTransaction($orderInfo);
                $result->setContents(Response::OK);
            }else{
                $result->setContents(Response::BAD);
            }
        }catch(\Exception $e){
            $this->messageManager->addExceptionMessage(
                $e, __($e->getMessage())
            );
            $this->redpayPayHelper->addTolog('error', 'Notify:' . $e->getMessage());
            $result->setContents(Response::BAD);
        }
        return $result;
    }
}
