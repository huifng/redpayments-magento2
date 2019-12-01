<?php

namespace Redpayments\Magento2\Model\Config\Source\Order\Status;

use Redpayments\Magento2\Payment\Payment;

/**
 * Order Status source model
 */
class Order
{
    public function toOptionArray()
    {
        $statuses = [
            Payment::PAY_PENDING => Payment::PAY_PENDING,
            Payment::PAY_PROCESSING => Payment::PAY_PROCESSING,
            // Payment::PAY_PAID => Payment::PAY_PAID,
            Payment::PAY_COMPLETED => Payment::PAY_COMPLETED,
        ];

        $options = [ [ 'value' => '', 'label' => __('-- Please Select --') ] ];
        foreach($statuses as $code => $label){
            $options[] = [ 'value' => $code, 'label' => $label ];
        }
        return $options;
    }
}
