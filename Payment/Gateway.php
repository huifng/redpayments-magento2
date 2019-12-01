<?php

namespace Redpayments\Magento2\Payment;

use function time;

class Gateway
{
    // const DOMAIN = 'http://huifng.vaiwan.com';
    const DOMAIN = 'https://dev-service.redpayments.com.au';
    // const DOMAIN = 'https://service.redpayments.com.au';
    /**
     * @var string
     */
    private $mchNo;
    /**
     * @var string
     */
    private $storeNo;
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var Request
     */
    private $request;

    public function __construct($mchNo, $storeNo, $apiKey, Request $request)
    {
        $this->mchNo = $mchNo;
        $this->storeNo = $storeNo;
        $this->apiKey = $apiKey;
        $this->request = $request;
    }

    public function createOrder($pay_method, $order_id, $amount, $currency, $description, 
        $redirect_url, $notify_url, $isMobile, $timeout = 0)
    {        
        $payWay = $isMobile ? 'WAP' : 'WEB';
        if('WechatPay' === $pay_method){
            $payWay = 'BUYER_SCAN_TRX_QRCODE';
        }
        $data = array(
            'mchNo' => $this->mchNo,
            'storeNo' =>$this->storeNo,
            'mchOrderNo' => $order_id,
            'channel' => 'Alipay'===$pay_method ? 'ALIPAY' : 'WECHAT',
            'payWay' => $payWay,
            'currency' => $currency,
            'amount' => $amount,
            'item' => $description,
            'quantity' => 1,
            'notifyUrl' => $notify_url,
            'returnUrl' => $redirect_url,
            'timestamp' => time()
        );

        if('Alipay' === $pay_method){
            $data['params'] = '{"referUrl":"https://magento2.test"}';
        }
        
        $result = $this->request->post(self::createUrl(), $data);
        
        if('Alipay'===$pay_method && isset($result['payForm']) && !empty($result['payForm'])){
            return $result['payForm'];
        }

        if('WechatPay'===$pay_method && isset($result['qrCode']) && !empty($result['qrCode'])){
            return $result['qrCode'];
        }

        return null;
    }

    public function cancel($trade_id)
    {
        return $this->request->post(self::cancelUrl(), array(
            'user' => $this->account->getUser(),
            'trade_id' => $trade_id
        ));
    }

    public function query($trade_id)
    {
        $result = $this->request->post(self::orderQueryUrl(), array(
            'mchNo' => $this->mchNo,
            'orderNo' => $trade_id,
            'channelOnly' => 'true'
        ));
        return $result;
    }

    public function refund($trade_id, $description)
    {
        return $this->request->post(self::orderQueryUrl(), array(
            'user' => $this->account->getUser(),
            'trade_id' => $trade_id,
            'description' => $description
        ));
    }

    // public function verify()
    // {
    //     $verify = $this->request->post(self::verifyUrl(), array(
    //         'user' => $this->account->getUser()
    //     ));
    //     if(isset($verify['info']) && empty($verify['info'])){
    //         return true;
    //     }
    //     return false;
    // }

    private static function generateUrl($action)
    {
        return self::DOMAIN . $action;
    }

    protected static function createUrl()
    {
        return self::generateUrl('/pay/gateway/create-order');
    }

    protected static function cancelUrl()
    {
        return self::generateUrl('/pay/gateway/cancel-order');
    }

    public static function orderQueryUrl()
    {
        return self::generateUrl('/pay/gateway/query-order');
    }

    protected static function refundUrl()
    {
        return self::generateUrl('/pay/gateway/refund-order');
    }

    // protected static function verifyUrl()
    // {
    //     return self::generateUrl('');
    // }
}