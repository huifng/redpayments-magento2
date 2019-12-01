<?php

namespace Redpayments\Magento2\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;
use function var_export;
use Redpayments\Magento2\Helper\General as RedpayPayHelper;
use Redpayments\Magento2\Logger\Logger;
use Redpayments\Magento2\Payment\Payment;

/**
 * Class PaymentConfigProvider
 *
 * @package Redpayments\Magento2\Model
 * @description
 * @version 1.0.0
 */
class PaymentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var AssetRepository
     */
    private $assetRepository;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var PaymentHelper
     */
    private $paymentHelper;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var RedpayPayHelper
     */
    private $redpayPayHelper;
    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * PaymentConfigProvider constructor.
     * @param PaymentHelper $paymentHelper
     * @param CheckoutSession $checkoutSession
     * @param AssetRepository $assetRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Escaper $escaper
     * @param RedpayPayHelper $redpayPayHelper
     * @param Logger $logger
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        CheckoutSession $checkoutSession,
        AssetRepository $assetRepository,
        ScopeConfigInterface $scopeConfig,
        Escaper $escaper,
        RedpayPayHelper $redpayPayHelper,
        Logger $logger
    )
    {
        $this->paymentHelper = $paymentHelper;
        $this->checkoutSession = $checkoutSession;
        $this->escaper = $escaper;
        $this->assetRepository = $assetRepository;
        $this->scopeConfig = $scopeConfig;
        $this->redpayPayHelper = $redpayPayHelper;
        $this->logger = $logger;
    }


    /**
     * Config Data for checkout
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $activeWechatPay = $this->redpayPayHelper->getIsActiveWechatPay();
        if( $activeWechatPay === true){
            $config['payment'][WechatPay::CODE]['isActive'] = true;
            $config['payment'][WechatPay::CODE]['title'] = empty($this->redpayPayHelper->getWechatPayDesc()) ? Payment::WECHAT : $this->redpayPayHelper->getWechatPayDesc();
        }else{
            $config['payment'][WechatPay::CODE]['isActive'] = false;
        }
        
        $activeAliPay = $this->redpayPayHelper->getIsActiveAlipay();
        if($activeAliPay === true){
            $config['payment'][AliPay::CODE]['isActive'] = true;
            $config['payment'][AliPay::CODE]['title'] = empty($this->redpayPayHelper->getAlipayDesc()) ? Payment::ALIPAY : $this->redpayPayHelper->getAlipayDesc();
        }else{
            $config['payment'][AliPay::CODE]['isActive'] = false;
        }

        return $config;
    }
}
