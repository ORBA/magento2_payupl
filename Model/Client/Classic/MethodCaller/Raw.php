<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\MethodCaller;

use Orba\Payupl\Model\Client\MethodCaller\RawInterface;

class Raw implements RawInterface
{
    /**
     * @var SoapClient\Order
     */
    protected $orderClient;

    /**
     * @var SoapClient\Refund
     */
    protected $refundClient;

    /**
     * @var PaytypesClient
     */
    protected $paytypesClient;

    /**
     * @param SoapClient\Order $orderClient
     * @param PaytypesClient $paytypesClient
     */
    public function __construct(
        SoapClient\Order $orderClient,
        SoapClient\Refund $refundClient,
        PaytypesClient $paytypesClient
    ) {
        $this->orderClient = $orderClient;
        $this->refundClient = $refundClient;
        $this->paytypesClient = $paytypesClient;
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
        return $this->orderClient->call('get', [
            'posId' => $posId,
            'sessionId' => $sessionId,
            'ts' => $ts,
            'sig' => $sig
        ]);
    }

    /**
     * @return array
     */
    public function getPaytypes()
    {
        $client = $this->paytypesClient->getClient();
        $client->send();
        $xml = new \SimpleXMLElement($client->getResponse()->getBody());
        $paytypes = [];
        foreach ($xml as $paytypeXml) {
            $paytypes[] = [
                'type' => (string)$paytypeXml->type,
                'name' => (string)$paytypeXml->name,
                'enable' => (string)$paytypeXml->enable === 'true',
                'img' => (string)$paytypeXml->img,
                'min' => (float)$paytypeXml->min,
                'max' => (float)$paytypeXml->max
            ];
        }
        return $paytypes;
    }

    /**
     * @param array $authData
     * @return \stdClass
     * @throws \Exception
     */
    public function refundGet(array $authData)
    {
        return $this->refundClient->call('getRefunds', ['RefundAuth' => $authData]);
    }

    /**
     * @param array $authData
     * @param array $addData
     * @return int
     */
    public function refundAdd(array $authData, array $addData)
    {
        return $this->refundClient->call('addRefund', [
            'RefundAuth' => $authData,
            'RefundData' => $addData
        ]);
    }
}
