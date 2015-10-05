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
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSession;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Validator::class, [
            'transactionResource' => $this->_transactionResource,
            'customerSession' => $this->_customerSession
        ]);
    }

    public function testValidateNoTransactionsFail()
    {
        $orderId = 1;
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->_transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->with($this->equalTo($orderId))->willReturn('ABC');
        $this->assertFalse($this->_model->validateNoTransactions($order));
    }

    public function testValidateNoTransactionsSuccess()
    {
        $orderId = 1;
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->_transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->with($this->equalTo($orderId))->willReturn(false);
        $this->assertTrue($this->_model->validateNoTransactions($order));
    }

    public function testValidatePaymentMethodFail()
    {
        $order = $this->_getOrderMock();
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $payment->expects($this->once())->method('getMethod')->willReturn('invalid');
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $this->assertFalse($this->_model->validatePaymentMethod($order));
    }

    public function testValidatePaymentMethodSuccess()
    {
        $order = $this->_getOrderMock();
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $payment->expects($this->once())->method('getMethod')->willReturn(\Orba\Payupl\Model\Payupl::CODE);
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $this->assertTrue($this->_model->validatePaymentMethod($order));
    }
    
    public function testValidateStateFailCancelled()
    {
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('getState')->willReturn(\Magento\Sales\Model\Order::STATE_CANCELED);
        $this->assertFalse($this->_model->validateState($order));
    }

    public function testValidateStateFailCompleted()
    {
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('getState')->willReturn(\Magento\Sales\Model\Order::STATE_COMPLETE);
        $this->assertFalse($this->_model->validateState($order));
    }

    public function testValidateStateFailClosed()
    {
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('getState')->willReturn(\Magento\Sales\Model\Order::STATE_CLOSED);
        $this->assertFalse($this->_model->validateState($order));
    }

    public function testValidateStateSuccess()
    {
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('getState')->willReturn('valid_state');
        $this->assertTrue($this->_model->validateState($order));
    }

    public function testValidateCustomerFail()
    {
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('getCustomerId')->willReturn(1);
        $this->_customerSession->expects($this->once())->method('getCustomerId')->willReturn(2);
        $this->assertFalse($this->_model->validateCustomer($order));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock()
    {
        return $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
    }
}