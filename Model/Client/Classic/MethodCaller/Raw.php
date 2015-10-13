<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\MethodCaller;

use Orba\Payupl\Model\Client\Exception;
use Orba\Payupl\Model\Client\MethodCaller\RawInterface;

class Raw implements RawInterface
{
    /**
     * @var RawClientInterface
     */
    protected $_orderClient;

    /**
     * @param SoapClient\Order $orderClient
     */
    public function __construct(
        SoapClient\Order $orderClient
    )
    {
        $this->_orderClient = $orderClient;
    }

    /**
     * @inheritdoc
     */
    public function call($methodName, array $args = [])
    {
        return call_user_func_array([$this, $methodName], $args);
    }

    /**
     * @param int $posId
     * @param string $sessionId
     * @param string $ts
     * @param string $sig
     * @return \stdClass
     * @throws \Exception
     */
    public function orderRetrieve($posId, $sessionId, $ts, $sig)
    {
        return $this->_orderClient->call('get', [
            'posId' => $posId,
            'sessionId' => $sessionId,
            'ts' => $ts,
            'sig' => $sig
        ]);
    }
}