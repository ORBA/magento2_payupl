<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Logger\Handler;

use Monolog\Logger;

class Exception extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/orba/payupl/exception.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::CRITICAL;
}
