<?php

namespace Redpayments\Magento2\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Redpayments\Magento2\Helper\General as RedpayPayHelper;
use Redpayments\Magento2\Payment\Payment;

class InstallData implements InstallDataInterface
{

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;
    /**
     * @var RedpayPayHelper $redpayPayHelper
     */
    private $redpayPayHelper;
    /**
     * InstallData constructor.
     *
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        RedpayPayHelper $redpayPayHelper
    )
    {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->redpayPayHelper = $redpayPayHelper;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create([ 'setup' => $setup ]);

        /**
         * Add 'redpayments_transaction_id' attributes for order
         */
        $salesSetup->addAttribute('order', Payment::META_TRANSACTION_ID, array( 'type' => 'varchar', 'visible' => false, 'required' => false ));
        $salesSetup->addAttribute('order', Payment::META_TRADE_ID, array( 'type' => 'varchar', 'visible' => false, 'required' => false ));
    }
}
