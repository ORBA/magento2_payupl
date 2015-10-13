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

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_orderClient = $this->getMockBuilder(SoapClient\Order::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Raw::class, [
            'orderClient' => $this->_orderClient
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

}