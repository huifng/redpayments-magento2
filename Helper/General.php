<?php

namespace Redpayments\Magento2\Helper;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Redpayments\Magento2\Logger\Logger;
use Redpayments\Magento2\Model\AliPay;
use Redpayments\Magento2\Model\WechatPay;
use Redpayments\Magento2\Payment\Gateway;
use Redpayments\Magento2\Payment\Cryptography;
use Redpayments\Magento2\Payment\Payment;
use Redpayments\Magento2\Payment\Request;
use Redpayments\Magento2\Payment\Utils;
use function implode;
use function round;
use function var_export;

/**
 * Class General
 *
 * @package Redpayments\Magento2\Helper
 * @description
 * @version 1.0.0
 */
class General extends AbstractHelper
{
    const ROUTER_NAME = 'redpayments';

    const MODULE_CODE = 'redpayments';
    const REDPAY_MERCHANT_NO = 'payment/' . self::MODULE_CODE . '/merchant_no';
    const REDPAY_STORE_NO = 'payment/' . self::MODULE_CODE . '/store_no';
    const REDPAY_API_KEY = 'payment/' . self::MODULE_CODE . '/api_key';

    const REDPAY_WECHATPAY_ACTIVE = 'payment/' . self::MODULE_CODE . '/wechatpay_active';
    const REDPAY_WECHATPAY_DESC = 'payment/' . self::MODULE_CODE . '/wechatpay_desc';
    const REDPAY_ALIPAY_ACTIVE = 'payment/' . self::MODULE_CODE . '/alipay_active';
    const REDPAY_ALIPAY_DESC = 'payment/' . self::MODULE_CODE . '/alipay_desc';
    const REDPAY_CURRENCY = 'payment/' . self::MODULE_CODE . '/currency';
    const REDPAY_DEBUG = 'payment/' . self::MODULE_CODE . '/debug';
    const REDPAY_FEE = 'payment/' . self::MODULE_CODE . '/fee';
    const REDPAY_IS_DEV = 'payment/' . self::MODULE_CODE . '/is_dev';
    const REDPAY_AUTO_EMAIL = 'payment/' . self::MODULE_CODE . '/auto_send_email';
    const REDPAY_AUTO_INVOICE = 'payment/' . self::MODULE_CODE . '/auto_invoice';

    const REDPAY_STATUS_PENDING = 'payment/' . self::MODULE_CODE . '/pending_status';
    const REDPAY_ORDER_PAID_STATUS = 'payment/' . self::MODULE_CODE . '/order_paid_status';

    const PAY_PRECREATED = 'PRECREATED';

    const PAY_PENDING = 'PRECREATED';

    const PAY_PROCESSING = 'PROCESSING';

    const PAY_PAID = 'SUCCEEDED';

    const PAY_CANCELLED = 'CANCELLED';

    const PAY_FAILED = 'FAILED';

    const PAY_REFUNDED = 'REFUNDED';

    const PAY_EXPIRED = 'EXPIRED';

    const PAY_COMPLETED = 'COMPLETED';
    
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ProductMetadataInterface
     */
    private $metadata;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Config
     */
    private $resourceConfig;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var Logger
     */
    private $logger;
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
     * @var Resolver
     */
    private $resolver;

    /**
     * @var Api
     */
    private static $gatewayInstance;

    /**
     * General constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Config $resourceConfig
     * @param ModuleListInterface $moduleList
     * @param ProductMetadataInterface $metadata
     * @param Resolver $resolver
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Config $resourceConfig,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $metadata,
        Resolver $resolver,
        Logger $logger
    )
    {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->resourceConfig = $resourceConfig;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->moduleList = $moduleList;
        $this->metadata = $metadata;
        $this->resolver = $resolver;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get admin value by path and storeId
     *
     * @param     $path
     * @param int $scopeCode
     *
     * @return mixed
     */
    public function getStoreConfig($path, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode
        );
    }

    public function getMchNo()
    {
        if($this->mchNo){
            return $this->mchNo;
        }
        $mchNo = trim(
            $this->getStoreConfig(self::REDPAY_MERCHANT_NO)
        );
        if(empty($mchNo)){
            $this->addTolog('error', 'Redpayments merchant no. not set');
            return null;
        }
        $this->mchNo = $mchNo;

        return $this->mchNo;
    }

    public function getStoreNo()
    {
        if($this->storeNo){
            return $this->storeNo;
        }
        $storeNo = trim(
            $this->getStoreConfig(self::REDPAY_STORE_NO)
        );
        if(empty($storeNo)){
            $this->addTolog('error', 'Redpayments store no. not set');
            return null;
        }
        $this->storeNo = $storeNo;

        return $this->storeNo;
    }

    public function getApiKey()
    {
        if($this->apiKey){
            return $this->apiKey;
        }
        $apiKey = trim(
            $this->getStoreConfig(self::REDPAY_API_KEY)
        );
        if(empty($apiKey)){
            $this->addTolog('error', 'Redpayments API key not set');
            return null;
        }
        $encryptor = $this->objectManager->get('Magento\Framework\Encryption\Encryptor');
        $apiKey = trim($encryptor->decrypt($apiKey));

        $this->apiKey = $apiKey;
        return $this->apiKey;
    }

    public function getIsActiveWechatPay()
    {
        return (bool)$this->getStoreConfig(self::REDPAY_WECHATPAY_ACTIVE);
    }

    public function getWechatPayDesc()
    {
        return $this->getStoreConfig(self::REDPAY_WECHATPAY_DESC);
    }

    public function getIsActiveAlipay()
    {
        return (bool)$this->getStoreConfig(self::REDPAY_ALIPAY_ACTIVE);
    }

    public function getAlipayDesc()
    {
        return $this->getStoreConfig(self::REDPAY_ALIPAY_DESC);
    }

    public function getPayCurrency()
    {
        return $this->getStoreConfig(self::REDPAY_CURRENCY);
    }

    /**
     * Selected processing status
     *
     * @param int $storeId
     *
     * @return mixed
     */
    public function getStatusProcessing()
    {
        return $this->getStoreConfig(self::REDPAY_ORDER_PAID_STATUS) ?: Payment::PAY_PROCESSING;
    }

    /**
     * Write to log
     *
     * @param $type
     * @param $data
     */
    public function addTolog($type, $data)
    {
        if($type == 'error'){
            $this->logger->addErrorLog($data);
        }else{
            $this->logger->addInfoLog($data);
        }
    }

    public function getGatewayInstance()
    {
        if(self::$gatewayInstance === null){
            $isDev = $this->getIsDev();
            $mchNo = $this->getMchNo();
            $storeNo = $this->getStoreNo();
            $apiKey = $this->getApiKey();
            self::$gatewayInstance = new Gateway($isDev, $mchNo, $storeNo, $apiKey, new Request(new Cryptography($apiKey)));
        }
        return self::$gatewayInstance;
    }

    public function getOrderPayUrl($paymentMethodCode, Order $order)
    {
        if($paymentMethodCode === AliPay::CODE){
            $paymentMethod = Payment::ALIPAY;
        }else{
            $paymentMethod = Payment::WECHAT;
        }

        $orderTotalAmount = $this->getOrderTotalAmount($order);
        $description = $this->getOrderProduct($order);
        $notifyUrl = $this->getNotifyUrl();
        $redirectUrl = $this->getRedirectUrl();

        return $this->getGatewayInstance()->createOrder($paymentMethod, $order->getId(), $orderTotalAmount, $this->getPayCurrency(), $description, $redirectUrl, $notifyUrl, Utils::isMobile());
    }

    protected function getOrderProduct(Order $order)
    {
        $productList = [];
        foreach($order->getAllItems() as $item){
            $product = $item->getData();
            if(isset($product['name']) && !empty($product['name'])){
                $productList[] = $product['name'];
            }
        }
        return implode(',', $productList);
    }

    /**
     * Redirect Url Builder /w OrderId & UTM No Override
     *
     * @param $orderId
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->urlBuilder->getUrl(self::ROUTER_NAME . '/checkout/success');
    }

    /**
     * Webhook Url Builder
     *
     * @return string
     */
    public function getNotifyUrl()
    {
        return $this->urlBuilder->getUrl(self::ROUTER_NAME . '/checkout/notify');
        //return 'http://huifng.vaiwan.com/redpayments/checkout/notify/';
    }

    /**
     * Checkout Url Builder
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->urlBuilder->getUrl('checkout/cart');
    }

    /**
     * Selected pending (payment) status
     *
     * @param int $storeId
     *
     * @return mixed
     */
    public function getStatusPending()
    {
        return $this->getStoreConfig(self::REDPAY_STATUS_PENDING) ?: Payment::PAY_NEW;
    }

    public function getFee()
    {
        $fee = $this->getStoreConfig(self::REDPAY_FEE);
        return $fee < 0 ? 0 : $fee;
    }

    public function getIsDev()
    {
        return (bool)$this->getStoreConfig(self::REDPAY_IS_DEV);
    }

    public function getAuthSendEmail()
    {
        return (bool)$this->getStoreConfig(self::REDPAY_AUTO_EMAIL);
    }

    public function getAuthInvoice()
    {
        return (bool)$this->getStoreConfig(self::REDPAY_AUTO_INVOICE);
    }

    /**
     * getOrderAmountByOrder
     *
     * @description
     * @version 1.0.0
     *
     * @param $order
     *
     * @return mixed
     */
    public function getOrderTotalAmount(Order $order)
    {
        $orderAmount = $order->getBaseGrandTotal();
        $fee = $this->getFee();
        if($fee > 0){
            $orderAmount += $orderAmount * ($fee / 100);
        }
        return (string)round($orderAmount, 2);
    }
}
