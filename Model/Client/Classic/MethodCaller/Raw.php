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
     * @var PaytypesClient
     */
    protected $_paytypesClient;

    /**
     * @param SoapClient\Order $orderClient
     * @param PaytypesClient $paytypesClient
     */
    public function __construct(
        SoapClient\Order $orderClient,
        PaytypesClient $paytypesClient
    )
    {
        $this->_orderClient = $orderClient;
        $this->_paytypesClient = $paytypesClient;
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

    public function getPaytypes()
    {
        $client = $this->_paytypesClient->getClient();
        $client->send();
        $xml = new \SimpleXMLElement($client->getResponse()->getBody());
        $paytypes = [];
        foreach ($xml as $paytypeXml) {
            $paytypes[] = [
                'type' => (string) $paytypeXml->type,
                'name' => (string) $paytypeXml->name,
                'enable' => (string) $paytypeXml->enable === 'true',
                'img' => (string) $paytypeXml->img,
                'min' => (float) $paytypeXml->min,
                'max' => (float) $paytypeXml->max
            ];
        }
        return $paytypes;
    }


}