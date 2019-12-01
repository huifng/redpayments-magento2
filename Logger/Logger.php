<?php

namespace Redpayments\Magento2\Logger;

class Logger extends \Monolog\Logger
{

    /**
     * Add info data to Mollie Log
     *
     * @param $type
     * @param $data
     */
    public function addInfoLog($data)
    {
        if(is_array($data) || is_object($data)){
            $this->addInfo(json_encode($data));
        }else{
            $this->addInfo($data);
        }
    }

    /**
     * Add error data to mollie Log
     *
     * @param $type
     * @param $data
     */
    public function addErrorLog($data)
    {
        if(is_array($data) || is_object($data)){
            $this->addError(json_encode($data));
        }else{
            $this->addError($data);
        }
    }
}
