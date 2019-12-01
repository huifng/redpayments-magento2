<?php

namespace Redpayments\Magento2\Controller\Checkout;

use function var_export;
use Redpayments\Magento2\Controller\Api;
use Redpayments\Magento2\Helper\General as RedpayPayHelper;

class Success extends Api
{
    /**
     * Execute Redirect to Mollie after placing order
     */
    public function execute()
    {
        try{
            $orderInfo = $this->getOrderInfo(false);
            $this->redpayPayHelper->addTolog('info', 'Success:' . var_export($orderInfo, true));

            if(isset($orderInfo['out_trade_no']) && isset($orderInfo['trade_no'])){
                $queryResult = $this->redpayPayHelper->getGatewayInstance()->query($orderInfo['out_trade_no']);
                $this->redpayPayHelper->addTolog('info', 'QueryResult: ' . var_export($queryResult, true));
                if(isset($queryResult['orderStatus']) && ('SUCCEEDED'===$queryResult['orderStatus'] 
                || 'COMPLETED'===$queryResult['orderStatus'])){
                    $queryResult['status'] = $queryResult['orderStatus'];
                    $this->paymentInstance->processTransaction($queryResult);
                    $this->_redirect(
                        'checkout/onepage/success?utm_nooverride=1'
                    );
                }else{
                    $this->_redirect('checkout/onepage/error?utm_nooverride=1&error=1');    
                }
            }else{
                $this->_redirect('checkout/onepage/error?utm_nooverride=1&error=1');
            }
        }catch(\Exception $e){
            $this->redpayPayHelper->addTolog('error', 'Success Exception:' . $e->getMessage());
            $this->messageManager->addExceptionMessage(
                $e, __($e->getMessage())
            );
            $this->_redirect('checkout/onepage/error?utm_nooverride=1&error=2');
        }
    }
}
