<?php

namespace Redpayments\Magento2\Model\Config\Source\Order\Status;


/**
 * Order Status source model
 */
class Pending
{
    public function toOptionArray()
    {
        $statuses = [
            'new' => 'new',
            'pending' => 'pending'
        ];

        $options = [ [ 'value' => '', 'label' => __('-- Please Select --') ] ];
        foreach($statuses as $code => $label){
            $options[] = [ 'value' => $code, 'label' => $label ];
        }
        return $options;
    }
}
