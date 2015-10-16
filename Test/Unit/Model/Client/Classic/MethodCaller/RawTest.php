<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\MethodCaller;

class RawTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Raw
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_refundClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paytypesClient;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_orderClient = $this->getMockBuilder(SoapClient\Order::class)->disableOriginalConstructor()->getMock();
        $this->_refundClient = $this->getMockBuilder(SoapClient\Refund::class)->disableOriginalConstructor()->getMock();
        $this->_paytypesClient = $this->getMockBuilder(PaytypesClient::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Raw::class, [
            'orderClient' => $this->_orderClient,
            'refundClient' => $this->_refundClient,
            'paytypesClient' => $this->_paytypesClient
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
        $this->_orderClient->expects($this->once())->method('call')->with(
            $this->equalTo('get'),
            $this->equalTo([
                'posId' => $posId,
                'sessionId' => $sessionId,
                'ts' => $ts,
                'sig' => $sig
            ])
        )->willThrowException($exception);
        $this->setExpectedException(\Exception::class, $exceptionMessage);
        $this->_model->orderRetrieve($posId, $sessionId, $ts, $sig);
    }

    public function testOrderRetrieveSuccess()
    {
        $posId = 123456;
        $sessionId = 'ABC';
        $ts = '123';
        $sig = 'DEF';
        $result = new \stdClass();
        $this->_orderClient->expects($this->once())->method('call')->with(
            $this->equalTo('get'),
            $this->equalTo([
                'posId' => $posId,
                'sessionId' => $sessionId,
                'ts' => $ts,
                'sig' => $sig
            ])
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->orderRetrieve($posId, $sessionId, $ts, $sig));
    }

    public function testGetPaytypesWithDisabled()
    {
        $xml = '<paytypes><paytype><type>t</type><name>płatnosc testowa</name><enable>false</enable><img>https://secure.payu.com/static/images/paytype/on-t.gif</img><min>0.50</min><max>1000.00</max></paytype></paytypes>';
        $result = $this->_getExemplaryPaytypeData(false);
        $client = $this->_getClientMock($xml);
        $this->_paytypesClient->expects($this->once())->method('getClient')->willReturn($client);
        $this->assertEquals($result, $this->_model->getPaytypes());
    }

    public function testGetPaytypesWithEnabled()
    {
        $xml = '<paytypes><paytype><type>t</type><name>płatnosc testowa</name><enable>true</enable><img>https://secure.payu.com/static/images/paytype/on-t.gif</img><min>0.50</min><max>1000.00</max></paytype></paytypes>';
        $result = $this->_getExemplaryPaytypeData(true);
        $client = $this->_getClientMock($xml);
        $this->_paytypesClient->expects($this->once())->method('getClient')->willReturn($client);
        $this->assertEquals($result, $this->_model->getPaytypes());
    }

    public function testRefundGetFail()
    {
        $authData = ['data'];
        $exceptionMessage = 'Exception message';
        $exception = new \Exception($exceptionMessage);
        $this->_refundClient->expects($this->once())->method('call')->with(
            $this->equalTo('getRefunds'),
            $this->equalTo(['RefundAuth' => $authData])
        )->willThrowException($exception);
        $this->setExpectedException(\Exception::class, $exceptionMessage);
        $this->_model->refundGet($authData);
    }

    public function testRefundGetSuccess()
    {
        $authData = ['data'];
        $result = new \stdClass();
        $this->_refundClient->expects($this->once())->method('call')->with(
            $this->equalTo('getRefunds'),
            $this->equalTo(['RefundAuth' => $authData])
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->refundGet($authData));
    }

    /**
     * @param $enable
     * @return array
     */
    protected function _getExemplaryPaytypeData($enable)
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
    protected function _getClientMock($xml)
    {
        $client = $this->getMockBuilder(\Zend\Http\Client::class)->disableOriginalConstructor()->getMock();
        $client->expects($this->once())->method('send');
        $response = $this->getMockBuilder(\Zend\Http\Response::class)->disableOriginalConstructor()->getMock();
        $response->expects($this->once())->method('getBody')->willReturn($xml);
        $client->expects($this->once())->method('getResponse')->willReturn($response);
        return $client;
    }

}