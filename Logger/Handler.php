<?php

namespace Redpayments\Magento2\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

/**
 * Class Handler
 *
 * @package Redpayments\Magento2\Logger
 */
class Handler extends Base
{

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
    /**
     * @var string
     */
    protected $fileName = '/var/log/redpayments.log';
}
