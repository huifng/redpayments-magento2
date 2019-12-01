<?php

namespace Redpayments\Magento2\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;
use Redpayments\Magento2\Helper\General as RedpayPayHelper;
use Redpayments\Magento2\Payment\Payment;
use function var_export;

/**
 * Class AbstractPayment
 * @package Redpay\Magento2\Model
 * @description
 * @version 1.0.0
 */
abstract class AbstractPayment extends AbstractMethod
{
    /**
     * Enable Initialize
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;
    /**
     * Enable Gateway
     *
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * Enable Refund
     *
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * Enable Partial Refund
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    protected $_canUseCheckout = true;

    protected $_canCapture = true;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var RedpayPayHelper
     */
    private $redpayPayHelper;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var Order
     */
    private $order;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var OrderSender
     */
    private $orderSender;
    /**
     * @var InvoiceSender
     */
    private $invoiceSender;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var InvoiceService
     */
    private $invoiceService;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Mollie constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param ObjectManagerInterface $objectManager
     * @param RedpayPayHelper $redpayPayHelper
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Order $order
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     * @param OrderRepository $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ObjectManagerInterface $objectManager,
        RedpayPayHelper $redpayPayHelper,
        CheckoutSession $checkoutSession,
        StoreManagerInterface $storeManager,
        Order $order,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        InvoiceService $invoiceService,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->objectManager = $objectManager;
        $this->redpayPayHelper = $redpayPayHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoiceService = $invoiceService;
        $this->registry = $registry;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initialize($paymentAction, $stateObject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->getInfoInstance();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        $status = $this->redpayPayHelper->getStatusPending();
        $this->redpayPayHelper->addTolog('info', 'Pending Status:' . var_export($status, true));
        $stateObject->setState(Order::STATE_NEW);
        $stateObject->setStatus($status);
        $stateObject->setIsNotified(false);
    }

    /**
     * startTransaction
     * @param Order $order
     * @return string
     */
    public function startTransaction(Order $order)
    {
        try{
            $orderPayUrl = $this->redpayPayHelper->getOrderPayUrl($order->getPayment()->getMethod(), $order);
            // if($this->redpayPayHelper->getAuthSendEmail()){
            //     $order->setCanSendNewEmailFlag(true);
            //     $this->orderSender->send($order);
            // }
            if(empty($orderPayUrl)){
                return $this->redpayPayHelper->getCheckoutUrl();
            }
            $message = __('Customer redirected to Redpayments, url: %1', $orderPayUrl);
            $status = $this->redpayPayHelper->getStatusPending();
            $order->addStatusToHistory($status, $message, false);
            $order->setStatus($status);
            $order->save();
            return $orderPayUrl;
        }catch(\Exception $e){
            $this->redpayPayHelper->addTolog('error', $e->getMessage());
            return $this->redpayPayHelper->getCheckoutUrl();
        }
    }

    /** 
     * processTransaction
     * @param array $orderInfo
     * @return array
     */
    public function processTransaction(array $orderInfo)
    {
        try{
            $order = $this->order->load($orderInfo['mchOrderNo']);
            if(empty($order)){
                $msg = 'Order not found';
                $this->redpayPayHelper->addTolog('error', $msg);
                return $msg;
            }
            $status = $orderInfo['status'];
            if(($status == RedpayPayHelper::PAY_PAID || $status == RedpayPayHelper::PAY_COMPLETED) 
                && $order->getStatus() !== Payment::PAY_PROCESSING){
                $processingStatus = $this->redpayPayHelper->getStatusProcessing();
                $this->redpayPayHelper->addTolog('info', 'Pending Status:' . var_export($processingStatus, true));
                $order->setState($processingStatus)
                    ->setStatus($processingStatus)
                    ->setData(Payment::META_TRANSACTION_ID, $orderInfo['channelOrderNo'])
                    ->setData(Payment::META_TRADE_ID, isset($orderInfo['payOrderId']) ? $orderInfo['payOrderId'] : $orderInfo['orderNo'])
                    ->save();
                $order = $this->order->load($orderInfo['mchOrderNo']);
                // if($this->redpayPayHelper->getAuthInvoice()){
                //     $this->autoBuildOrderInvoice($order);
                // }
                // if($this->redpayPayHelper->getAuthSendEmail()){
                //     $this->orderSender->send($order);
                // }
                $msg = [ 'success' => true, 'status' => 'paid', 'order_id' => $orderInfo['mchOrderNo'] ];
                $this->redpayPayHelper->addTolog('success', $msg);
                return $msg;
            }

            $msg = [ 'success' => false, 'status' => $status, 'order_id' => $orderInfo['mchOrderNo'] ];
            return $msg;
        }catch(\Exception $e){
            $msg = [ 'error' => true, 'msg' => $e->getMessage() ];
            $this->redpayPayHelper->addTolog('error', $msg);
            return $msg;
        }
    }

    protected function autoBuildOrderInvoice(\Magento\Sales\Model\Order $order)
    {
        if(!$order->getId()){
            throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
        }

        if(!$order->canInvoice()){
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The order does not allow an invoice to be created.')
            );
        }

        $invoice = $this->invoiceService->prepareInvoice($order, []);

        if(!$invoice){
            throw new LocalizedException(__('We can\'t save the invoice right now.'));
        }

        if(!$invoice->getTotalQty()){
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t create an invoice without products.')
            );
        }
        $this->registry->register('current_invoice', $invoice);

        $invoice->register();

        $invoice->getOrder()->setCustomerNoteNotify(true);
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->setSendEmail(true);

        $transactionSave = $this->objectManager->create(
            \Magento\Framework\DB\Transaction::class
        )->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );

        $transactionSave->save();
        try{
            $sendStatus = $this->invoiceSender->send($invoice, true);
            if($sendStatus){
                $this->redpayPayHelper->addTolog('info', 'Invoice Send Email Success');
            }else{
                $this->redpayPayHelper->addTolog('error', 'Invoice Send Email Failed');
            }
        }catch(\Exception $e){
            $this->redpayPayHelper->addTolog('info', 'Invoice Send Email:' . $e->getMessage());
        }

    }
}
