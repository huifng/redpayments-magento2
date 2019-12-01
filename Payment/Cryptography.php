<?php

namespace Redpayments\Magento2\Payment;

use function array_filter;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Class Cryptography
 *
 * @package Redpayments
 * @description
 * @version 1.0.0
 */
class Cryptography
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $algorithm;
    /**
     * @var string
     */
    private $algo;

    public function __construct($key, $algorithm = 'md5', $algo = 'sha256')
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
        $this->algo = $algo;
    }

    public function encrypt(array $data, $sort = false)
    {
        switch($this->algorithm){
            case 'hash_hmac':
                {
                    switch($this->algo){
                        case 'sha256':
                            {
                                if(\function_exists('hash_hmac') === false){
                                    throw new \InvalidArgumentException('hash_hmac not found .');
                                }
                                if($sort === true){
                                    \ksort($data);
                                }
                                return \hash_hmac('sha256', \is_array($data) ? self::serialize($data) : $data, $this->token);
                            }
                    }
                }
            case 'md5':
                {
                    if($sort === true){
                        \ksort($data);
                        \reset($data);
                    }
                    $prestr = \is_array($data) ? self::serialize($data) : $data;
                    $prestr = $prestr . '&key=' . $this->key;
                    return md5($prestr);
                }

        }
        throw new \InvalidArgumentException('Encryption algorithm not found.');
    }

    protected static function serialize(array $data = [])
    {
        $serialize = [];
        foreach($data as $key => $val){
            if('sign'!==$key && !empty($val)){
                $serialize[] = $key . '=' . $val;
            }
        }
        return implode('&', $serialize);
    }

    /**
     * verify
     *
     * @param $data
     * @return bool
     */
    public function verify($data, $sort = true)
    {
        $sign = $this->encrypt($data, $sort);
        return 0===strnatcasecmp($data['sign'],$sign);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @param string $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @return string
     */
    public function getAlgo()
    {
        return $this->algo;
    }

    /**
     * @param string $algo
     */
    public function setAlgo($algo)
    {
        $this->algo = $algo;
    }
}