<?php

namespace Redpayments\Magento2\Payment;

use function strlen;
use function substr;
use function var_export;

class Notify
{
    /**
     * @var Cryptography
     */
    private $cryptography;

    public function __construct(Cryptography $cryptography)
    {
        $this->cryptography = $cryptography;
    }

    protected function input()
    {
        $result = \file_get_contents('php://input');
        if(!empty($result)){
            $result = \urldecode($result);
        }else{
            if(!isset($_GET) || empty($_GET)){
                throw new \InvalidArgumentException('Receiver Data Is Empty');
            }else{
                $result = $_GET;
            }
        }

        if(empty($result)){
            throw new \InvalidArgumentException('Receiver Data Is Empty');
        }
        if(!is_array($result)){
            $result = \json_decode($result, true);
            if(\json_last_error()){
                throw new \InvalidArgumentException('Receiver Data Is Error, ' . \json_last_error_msg());
            }
        }
        // if(!isset($result['sign']) || empty($result['sign'])){
        //     throw new \InvalidArgumentException('Receiver Data Is Invalid');
        // }
        return $result;
    }

    public function getOrderInfo($signVerificationNeeded)
    {
        $data = $this->input();
        if($signVerificationNeeded && $this->cryptography->verify($data) === false){
            throw new \InvalidArgumentException('Receiver Data Signature Verification Failed');
        }
        return $data;
    }

    /**
     * @return Cryptography
     */
    public function getCryptography()
    {
        return $this->cryptography;
    }

    /**
     * @param Cryptography $cryptography
     */
    public function setCryptography(Cryptography $cryptography)
    {
        $this->cryptography = $cryptography;
    }
}