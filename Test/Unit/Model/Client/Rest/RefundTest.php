<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Refund
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_methodCaller;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dataValidator = $this->getMockBuilder(Refund\DataValidator::class)->getMock();
        $this->_methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            Refund::class,
            [
                'dataValidator' => $this->_dataValidator,
                'methodCaller' => $this->_methodCaller
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

    public function testCreateFail()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('refundCreate'),
            $this->equalTo([$orderId, $description, $amount])
        )->willReturn(false);
        $this->assertFalse($this->_model->create($orderId, $description, $amount));
    }

    public function testCreateSuccess()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $result = new \stdClass();
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('refundCreate'),
            $this->equalTo([$orderId, $description, $amount])
        )->willReturn($result);
        $this->assertTrue($this->_model->create($orderId, $description, $amount));
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