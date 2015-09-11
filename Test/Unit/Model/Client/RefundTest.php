<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client\Refund
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sdk;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dataValidator = $this->getMockBuilder(Refund\DataValidator::class)->getMock();
        $this->_sdk = $this->getMockBuilder(Sdk::class)->getMock();
        $this->_logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            Refund::class,
            [
                'dataValidator' => $this->_dataValidator,
                'sdk' => $this->_sdk,
                'logger' => $this->_logger
            ]
        );
    }

    public function testValidateCreateFailedEmptyOrderId()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validateCreate('', '', 0));
    }

    public function testValidateCreateFailedEmptyDescription()
    {
        $orderId = '123456';
        $this->_dataValidator->method('validateEmpty')->will($this->onConsecutiveCalls(true, false));
        $this->assertFalse($this->_model->validateCreate($orderId, '', 0));
    }

    public function testValidateCreateFailedInvalidAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = 'invalid';
        $this->_expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->_dataValidator->expects($this->once())->method('validatePositiveInt')->willReturn(false);
        $this->assertFalse($this->_model->validateCreate($orderId, $description, $amount));
    }

    public function testValidateCreateSuccessNoAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $this->_expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->assertTrue($this->_model->validateCreate($orderId, $description));
    }

    public function testValidateCreateSuccessWithAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $this->_expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->_dataValidator->expects($this->once())->method('validatePositiveInt')->with($this->equalTo($amount))->willReturn(true);
        $this->assertTrue($this->_model->validateCreate($orderId, $description, $amount));
    }

    public function testCreateSuccess()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $result = $this->_getResultMock();
        $this->_sdk->expects($this->once())->method('refundCreate')->with(
            $this->equalTo($orderId),
            $this->equalTo($description),
            $this->equalTo($amount)
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->create($orderId, $description, $amount));
    }

    public function testCreateFail()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $exception = new \Exception();
        $this->_sdk->expects($this->once())->method('refundCreate')->will($this->throwException($exception));
        $this->_logger->expects($this->once())->method('critical')->with($exception);
        $this->assertFalse($this->_model->create($orderId, $description, $amount));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getResultMock()
    {
        return $this->getMockBuilder(\OpenPayU_Result::class)->getMock();
    }

    /**
     * @param string $orderId
     * @param string $description
     */
    protected function _expectNotEmptyOrderIdAndDescription($orderId, $description)
    {
        $this->_dataValidator->method('validateEmpty')->withConsecutive(
            [$this->equalTo($orderId)],
            [$this->equalTo($description)]
        )->will($this->onConsecutiveCalls(true, true));
    }

}