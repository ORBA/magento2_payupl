<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Orba\Payupl\Model\Client\Exception;

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
        $this->_rawMethod = $this->getMockBuilder(MethodCaller\RawInterface::class)->getMockForAbstractClass();
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

    public function testSuccess()
    {
        $methodName = 'method';
        $args = ['args'];
        $result = $this->getMockBuilder(\OpenPayU_Result::class)->getMock();
        $this->_rawMethod->expects($this->once())->method('call')->with(
            $this->equalTo($methodName),
            $this->equalTo($args)
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->call($methodName, $args));
    }
}