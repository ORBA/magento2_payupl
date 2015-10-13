<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Orba\Payupl\Model\Client\MethodCallerInterface;
use Orba\Payupl\Model\Client\Exception;

class MethodCaller implements MethodCallerInterface
{
    /**
     * @var MethodCaller\RawInterface
     */
    protected $_rawMethod;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $_logger;

    /**
     * @param MethodCaller\RawInterface $rawMethod
     * @param \Orba\Payupl\Logger\Logger $logger
     */
    public function __construct(
        MethodCaller\RawInterface $rawMethod,
        \Orba\Payupl\Logger\Logger $logger
    )
    {
        $this->_rawMethod = $rawMethod;
        $this->_logger = $logger;
    }

    /**
     * @param string $methodName
     * @param array $args
     * @return \stdClass|false
     */
    public function call($methodName, array $args = [])
    {
        try {
            return $this->_rawMethod->call($methodName, $args);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return false;
        }
    }
}