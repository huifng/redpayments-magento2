<?php

namespace Redpayments\Magento2\Model\Config\Source;


class Currency
{
    public function toOptionArray()
    {
        $currency = [
            'CNY' => 'CNY',
            'AUD' => 'AUD'
        ];

        $options = [ [ 'value' => '', 'label' => __('-- Please Select --') ] ];
        foreach($currency as $code => $label){
            $options[] = [ 'value' => $code, 'label' => $label ];
        }
        return $options;
    }
}