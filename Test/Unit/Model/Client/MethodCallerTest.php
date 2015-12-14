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
    protected $rawMethod;

    /**
     * @var MethodCaller
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->rawMethod = $this->getMockBuilder(MethodCaller\RawInterface::class)->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            MethodCaller::class,
            [
                'rawMethod' => $this->rawMethod,
                'logger' => $this->logger
            ]
        );
    }

    public function testFailException()
    {
        $methodName = 'method';
        $args = ['args'];
        $exception = new \Exception();
        $this->rawMethod->expects($this->once())->method('call')->with(
            $this->equalTo($methodName),
            $this->equalTo($args)
        )->will($this->throwException($exception));
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->assertFalse($this->model->call($methodName, $args));
    }

    public function testSuccess()
    {
        $methodName = 'method';
        $args = ['args'];
        $result = $this->getMockBuilder(\OpenPayU_Result::class)->getMock();
        $this->rawMethod->expects($this->once())->method('call')->with(
            $this->equalTo($methodName),
            $this->equalTo($args)
        )->willReturn($result);
        $this->assertEquals($result, $this->model->call($methodName, $args));
    }
}
