<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\MethodCaller;

class RawTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Raw
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $refundClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paytypesClient;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderClient = $this->getMockBuilder(SoapClient\Order::class)->disableOriginalConstructor()->getMock();
        $this->refundClient = $this->getMockBuilder(SoapClient\Refund::class)->disableOriginalConstructor()->getMock();
        $this->paytypesClient = $this->getMockBuilder(PaytypesClient::class)->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(Raw::class, [
            'orderClient' => $this->orderClient,
            'refundClient' => $this->refundClient,
            'paytypesClient' => $this->paytypesClient
        ]);
    }

    public function testOrderRetrieveFail()
    {
        $posId = 123456;
        $sessionId = 'ABC';
        $ts = '123';
        $sig = 'DEF';
        $exceptionMessage = 'Exception message';
        $exception = new \Exception($exceptionMessage);
        $this->orderClient->expects($this->once())->method('call')->with(
            $this->equalTo('get'),
            $this->equalTo([
                'posId' => $posId,
                'sessionId' => $sessionId,
                'ts' => $ts,
                'sig' => $sig
            ])
        )->willThrowException($exception);
        $this->expectException(\Exception::class, $exceptionMessage);
        $this->model->orderRetrieve($posId, $sessionId, $ts, $sig);
    }

    public function testOrderRetrieveSuccess()
    {
        $posId = 123456;
        $sessionId = 'ABC';
        $ts = '123';
        $sig = 'DEF';
        $result = new \stdClass();
        $this->orderClient->expects($this->once())->method('call')->with(
            $this->equalTo('get'),
            $this->equalTo([
                'posId' => $posId,
                'sessionId' => $sessionId,
                'ts' => $ts,
                'sig' => $sig
            ])
        )->willReturn($result);
        $this->assertEquals($result, $this->model->orderRetrieve($posId, $sessionId, $ts, $sig));
    }

    public function testGetPaytypesWithDisabled()
    {
        $xml = '<paytypes><paytype><type>t</type><name>płatnosc testowa</name><enable>false</enable>
            <img>https://secure.payu.com/static/images/paytype/on-t.gif</img><min>0.50</min><max>1000.00</max>
            </paytype></paytypes>';
        $result = $this->getExemplaryPaytypeData(false);
        $client = $this->getClientMock($xml);
        $this->paytypesClient->expects($this->once())->method('getClient')->willReturn($client);
        $this->assertEquals($result, $this->model->getPaytypes());
    }

    public function testGetPaytypesWithEnabled()
    {
        $xml = '<paytypes><paytype><type>t</type><name>płatnosc testowa</name><enable>true</enable>
            <img>https://secure.payu.com/static/images/paytype/on-t.gif</img><min>0.50</min><max>1000.00</max>
            </paytype></paytypes>';
        $result = $this->getExemplaryPaytypeData(true);
        $client = $this->getClientMock($xml);
        $this->paytypesClient->expects($this->once())->method('getClient')->willReturn($client);
        $this->assertEquals($result, $this->model->getPaytypes());
    }

    public function testRefundGetFail()
    {
        $authData = ['data'];
        $exceptionMessage = 'Exception message';
        $exception = new \Exception($exceptionMessage);
        $this->refundClient->expects($this->once())->method('call')->with(
            $this->equalTo('getRefunds'),
            $this->equalTo(['RefundAuth' => $authData])
        )->willThrowException($exception);
        $this->expectException(\Exception::class, $exceptionMessage);
        $this->model->refundGet($authData);
    }

    public function testRefundGetSuccess()
    {
        $authData = ['data'];
        $result = new \stdClass();
        $this->refundClient->expects($this->once())->method('call')->with(
            $this->equalTo('getRefunds'),
            $this->equalTo(['RefundAuth' => $authData])
        )->willReturn($result);
        $this->assertEquals($result, $this->model->refundGet($authData));
    }

    /**
     * @param $enable
     * @return array
     */
    protected function getExemplaryPaytypeData($enable)
    {
        return [
            [
                'type' => 't',
                'name' => 'płatnosc testowa',
                'enable' => $enable,
                'img' => 'https://secure.payu.com/static/images/paytype/on-t.gif',
                'min' => 0.5,
                'max' => 1000
            ]
        ];
    }

    /**
     * @param $xml
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock($xml)
    {
        $client = $this->getMockBuilder(\Zend\Http\Client::class)->disableOriginalConstructor()->getMock();
        $client->expects($this->once())->method('send');
        $response = $this->getMockBuilder(\Zend\Http\Response::class)->disableOriginalConstructor()->getMock();
        $response->expects($this->once())->method('getBody')->willReturn($xml);
        $client->expects($this->once())->method('getResponse')->willReturn($response);
        return $client;
    }
}
