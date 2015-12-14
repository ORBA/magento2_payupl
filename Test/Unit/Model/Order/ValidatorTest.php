<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Validator
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(Validator::class, [
            'transactionResource' => $this->transactionResource,
            'customerSession' => $this->customerSession
        ]);
    }

    public function testValidateNoTransactionsFail()
    {
        $orderId = 1;
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')
            ->with($this->equalTo($orderId))->willReturn('ABC');
        $this->assertFalse($this->model->validateNoTransactions($order));
    }

    public function testValidateNoTransactionsSuccess()
    {
        $orderId = 1;
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')
            ->with($this->equalTo($orderId))->willReturn(false);
        $this->assertTrue($this->model->validateNoTransactions($order));
    }

    public function testValidatePaymentMethodFail()
    {
        $order = $this->getOrderMock();
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->once())->method('getMethod')->willReturn('invalid');
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $this->assertFalse($this->model->validatePaymentMethod($order));
    }

    public function testValidatePaymentMethodSuccess()
    {
        $order = $this->getOrderMock();
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->once())->method('getMethod')->willReturn(\Orba\Payupl\Model\Payupl::CODE);
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $this->assertTrue($this->model->validatePaymentMethod($order));
    }
    
    public function testValidateStateFailCancelled()
    {
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getState')->willReturn(\Magento\Sales\Model\Order::STATE_CANCELED);
        $this->assertFalse($this->model->validateState($order));
    }

    public function testValidateStateFailCompleted()
    {
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getState')->willReturn(\Magento\Sales\Model\Order::STATE_COMPLETE);
        $this->assertFalse($this->model->validateState($order));
    }

    public function testValidateStateFailClosed()
    {
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getState')->willReturn(\Magento\Sales\Model\Order::STATE_CLOSED);
        $this->assertFalse($this->model->validateState($order));
    }

    public function testValidateStateSuccess()
    {
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getState')->willReturn('valid_state');
        $this->assertTrue($this->model->validateState($order));
    }

    public function testValidateCustomerFail()
    {
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getCustomerId')->willReturn(1);
        $this->customerSession->expects($this->once())->method('getCustomerId')->willReturn(2);
        $this->assertFalse($this->model->validateCustomer($order));
    }

    public function testValidateNotPaidFail()
    {
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getTotalPaid')->willReturn(10);
        $this->assertFalse($this->model->validateNotPaid($order));
    }

    public function testValidateNotPaidSuccess()
    {
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getTotalPaid')->willReturn(null);
        $this->assertTrue($this->model->validateNotPaid($order));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock()
    {
        return $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
    }
}
