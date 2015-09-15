<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class MethodCallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rawMethod;

    /**
     * @var MethodCaller
     */
    protected $_model;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_rawMethod = $this->getMockBuilder(MethodCaller\Raw::class)->setMethods(['call'])->getMock();
        $this->_logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            MethodCaller::class,
            [
                'rawMethod' => $this->_rawMethod,
                'logger' => $this->_logger
            ]
        );
    }

    public function testFailException()
    {
        $methodName = 'method';
        $args = ['args'];
        $exception = new \Exception();
        $this->_rawMethod->expects($this->once())->method('call')->with(
            $this->equalTo($methodName),
            $this->equalTo($args)
        )->will($this->throwException($exception));
        $this->_logger->expects($this->once())->method('critical')->with($exception);
        $this->assertFalse($this->_model->call($methodName, $args));
    }

    public function testFailStatus()
    {
        $methodName = 'method';
        $args = ['args'];
        $result = $this->getMockBuilder(\OpenPayU_Result::class)->getMock();
        $response = new \stdClass();
        $status = new \stdClass();
        $status->statusCode = 'ERROR';
        $response->status = $status;
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $this->_rawMethod->expects($this->once())->method('call')->with(
            $this->equalTo($methodName),
            $this->equalTo($args)
        )->willReturn($result);
        $exception = new Exception(\Zend_Json::encode($status));
        $this->_logger->expects($this->once())->method('critical')->with($exception);
        $this->assertFalse($this->_model->call($methodName, $args));
    }

    public function testSuccess()
    {
        $methodName = 'method';
        $args = ['args'];
        $result = $this->getMockBuilder(\OpenPayU_Result::class)->getMock();
        $response = new \stdClass();
        $status = new \stdClass();
        $status->statusCode = 'SUCCESS';
        $response->status = $status;
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $this->_rawMethod->expects($this->once())->method('call')->with(
            $this->equalTo($methodName),
            $this->equalTo($args)
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->call($methodName, $args));
    }
}