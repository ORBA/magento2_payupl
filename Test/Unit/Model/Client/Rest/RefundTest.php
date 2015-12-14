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
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodCaller;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->dataValidator = $this->getMockBuilder(Refund\DataValidator::class)->getMock();
        $this->methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->model = $objectManagerHelper->getObject(
            Refund::class,
            [
                'dataValidator' => $this->dataValidator,
                'methodCaller' => $this->methodCaller
            ]
        );
    }

    public function testValidateCreateFailedEmptyOrderId()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->model->validateCreate('', '', 0));
    }

    public function testValidateCreateFailedEmptyDescription()
    {
        $orderId = '123456';
        $this->dataValidator->method('validateEmpty')->will($this->onConsecutiveCalls(true, false));
        $this->assertFalse($this->model->validateCreate($orderId, '', 0));
    }

    public function testValidateCreateFailedInvalidAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = 'invalid';
        $this->expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->dataValidator->expects($this->once())->method('validatePositiveInt')->willReturn(false);
        $this->assertFalse($this->model->validateCreate($orderId, $description, $amount));
    }

    public function testValidateCreateSuccessNoAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $this->expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->assertTrue($this->model->validateCreate($orderId, $description));
    }

    public function testValidateCreateSuccessWithAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $this->expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->dataValidator->expects($this->once())->method('validatePositiveInt')->with($this->equalTo($amount))
            ->willReturn(true);
        $this->assertTrue($this->model->validateCreate($orderId, $description, $amount));
    }

    public function testCreateFail()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('refundCreate'),
            $this->equalTo([$orderId, $description, $amount])
        )->willReturn(false);
        $this->assertFalse($this->model->create($orderId, $description, $amount));
    }

    public function testCreateSuccess()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $result = new \stdClass();
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('refundCreate'),
            $this->equalTo([$orderId, $description, $amount])
        )->willReturn($result);
        $this->assertTrue($this->model->create($orderId, $description, $amount));
    }

    /**
     * @param string $orderId
     * @param string $description
     */
    protected function expectNotEmptyOrderIdAndDescription($orderId, $description)
    {
        $this->dataValidator->method('validateEmpty')->withConsecutive(
            [$this->equalTo($orderId)],
            [$this->equalTo($description)]
        )->will($this->onConsecutiveCalls(true, true));
    }
}
