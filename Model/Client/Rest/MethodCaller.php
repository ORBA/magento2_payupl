<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Orba\Payupl\Model\Client\MethodCallerInterface;
use Orba\Payupl\Model\Client\Exception;

class MethodCaller implements MethodCallerInterface
{
    /**
     * @var MethodCaller\Raw
     */
    protected $_rawMethod;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $_logger;

    /**
     * @param MethodCaller\Raw $rawMethod
     * @param \Orba\Payupl\Logger\Logger $logger
     */
    public function __construct(
        MethodCaller\Raw $rawMethod,
        \Orba\Payupl\Logger\Logger $logger
    )
    {
        $this->_rawMethod = $rawMethod;
        $this->_logger = $logger;
    }

    /**
     * @param string $methodName
     * @param array $args
     * @return bool|\OpenPayU_Result
     */
    public function call($methodName, array $args = [])
    {
        try {
            $result = $this->_rawMethod->call($methodName, $args);
            $response = $result->getResponse();
            if (isset($response->status)) {
                $status = $response->status;
                if ((string)$status->statusCode !== 'SUCCESS') {
                    throw new Exception(\Zend_Json::encode($status));
                }
            }
            return $result;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return false;
        }
    }
}