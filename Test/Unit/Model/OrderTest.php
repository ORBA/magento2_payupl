<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Order
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionCollectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSuccessValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderValidator;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_transactionCollectionFactory = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction\CollectionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_transactionFactory = $this->getMockBuilder(\Orba\Payupl\Model\TransactionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_orderFactory = $this->getMockBuilder(\Orba\Payupl\Model\Sales\OrderFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_checkoutSuccessValidator = $this->getMockBuilder(\Magento\Checkout\Model\Session\SuccessValidator::class)->disableOriginalConstructor()->getMock();
        $this->_checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()->setMethods(['getLastOrderId'])->getMock();
        $this->_request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $this->_orderValidator = $this->getMockBuilder(Order\Validator::class)->disableOriginalConstructor()->getMock();
        $this->_model = $this->getMockForAbstractClass(Order::class, [
            'transactionCollectionFactory' => $this->_transactionCollectionFactory,
            'transactionFactory' => $this->_transactionFactory,
            'transactionResource' => $this->_transactionResource,
            'orderFactory' => $this->_orderFactory,
            'checkoutSuccessValidator' => $this->_checkoutSuccessValidator,
            'checkoutSession' => $this->_checkoutSession,
            'request' => $this->_request,
            'orderValidator' => $this->_orderValidator
        ]);
    }

    public function testSaveNewTransaction()
    {
        $orderId = '1';
        $payuplOrderId = 'Z963D5JQR2230925GUEST000P01';
        $payuplExternalOrderId = '0000000001:1';
        $status = 'status';
        $lastTry = 3;
        $transactionCollection = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction\Collection::class)->disableOriginalConstructor()->getMock();
        $transactionCollection->expects($this->once())->method('addFieldToFilter')->with(
            $this->equalTo('order_id'),
            $this->equalTo($orderId)
        )->will($this->returnSelf());
        $transactionCollection->expects($this->once())->method('setOrder')->with(
            $this->equalTo('try'),
            $this->equalTo(\Magento\Framework\Data\Collection::SORT_ORDER_DESC)
        )->will($this->returnSelf());
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getTry')->willReturn($lastTry);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $transactionToSave = $this->_getTransactionMock();
        $transactionToSave->expects($this->once())->method('setOrderId')->with($this->equalTo($orderId))->will($this->returnSelf());
        $transactionToSave->expects($this->once())->method('setPayuplOrderId')->with($this->equalTo($payuplOrderId))->will($this->returnSelf());
        $transactionToSave->expects($this->once())->method('setPayuplExternalOrderId')->with($this->equalTo($payuplExternalOrderId))->will($this->returnSelf());
        $transactionToSave->expects($this->once())->method('setTry')->with($this->equalTo($lastTry + 1))->will($this->returnSelf());
        $transactionToSave->expects($this->once())->method('setStatus')->with($this->equalTo($status))->will($this->returnSelf());
        $transactionToSave->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_transactionFactory->expects($this->once())->method('create')->willReturn($transactionToSave);
        $this->_model->saveNewTransaction($orderId, $payuplOrderId, $payuplExternalOrderId, $status);
    }

    public function testGetOrderByIdFailNotFound()
    {
        $orderId = 1;
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('load')->with($this->equalTo($orderId))->will($this->returnSelf());
        $order->expects($this->once())->method('getId')->willReturn(false);
        $this->_orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->assertFalse($this->_model->loadOrderById($orderId));
    }

    public function testGetOrderByIdSuccess()
    {
        $orderId = 1;
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('load')->with($this->equalTo($orderId))->will($this->returnSelf());
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->_orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->assertEquals($order, $this->_model->loadOrderById($orderId));
    }

    public function testGetOrderByPayuplOrderIdFailNotFound()
    {
        $payuplOrderId = 'ABC';
        $this->_transactionResource->expects($this->once())->method('getOrderIdByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn(false);
        $this->assertFalse($this->_model->loadOrderByPayuplOrderId($payuplOrderId));
    }

    public function testGetOrderByPayuplOrderIdSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'ABC';
        $this->_transactionResource->expects($this->once())->method('getOrderIdByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn($orderId);
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('load')->with($this->equalTo($orderId))->will($this->returnSelf());
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->_orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->assertEquals($order, $this->_model->loadOrderByPayuplOrderId($payuplOrderId));
    }

    public function testSetNewOrderStatus()
    {
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('setState')->with($this->equalTo(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT))->will($this->returnSelf());
        $order->expects($this->once())->method('addStatusToHistory')->with($this->equalTo(true))->will($this->returnSelf());
        $order->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_model->setNewOrderStatus($order);
    }

    public function testSetHoldedOrderStatus()
    {
        $status = 'REJECTED';
        $orderStateOld = 'old_state';
        $orderStatusOld = 'old_status';
        $orderStateNew = Sales\Order::STATE_HOLDED;
        $orderStatusNew = 'new_status';
        $order = $this->_getOrderMock();
        $config = $this->getMockBuilder(Sales\Order\Config::class)->disableOriginalConstructor()->getMock();
        $config->expects($this->once())->method('getStateDefaultStatus')->with($this->equalTo($orderStateNew))->willReturn($orderStatusNew);
        $order->expects($this->once())->method('getConfig')->willReturn($config);
        $order->expects($this->once())->method('getState')->willReturn($orderStateOld);
        $order->expects($this->once())->method('getStatus')->willReturn($orderStatusOld);
        $order->expects($this->once())->method('setHoldBeforeState')->with($this->equalTo($orderStateOld))->will($this->returnSelf());
        $order->expects($this->once())->method('setHoldBeforeStatus')->with($this->equalTo($orderStatusOld))->will($this->returnSelf());
        $order->expects($this->once())->method('setState')->with($this->equalTo($orderStateNew))->will($this->returnSelf());
        $order->expects($this->once())->method('setStatus')->with($this->equalTo($orderStatusNew))->will($this->returnSelf());
        $order->expects($this->once())->method('addStatusHistoryComment')->with($this->equalTo(__('Payu.pl status') . ': ' . $status));
        $order->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_model->setHoldedOrderStatus($order, $status);
    }

    public function testCompletePayment()
    {
        $amount = 2.22;
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $payment->expects($this->once())->method('registerCaptureNotification')->with($this->equalTo($amount))->will($this->returnSelf());
        $payment->expects($this->once())->method('save');
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $invoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)->disableOriginalConstructor()->getMock();
        $invoice->expects($this->once())->method('save');
        $order->expects($this->once())->method('getRelatedObjects')->willReturn([$invoice]);
        $order->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_model->completePayment($order, $amount);
    }

    public function testGetOrderIdForPaymentStartSuccessNewOrder()
    {
        $orderId = 1;
        $this->_checkoutSuccessValidator->expects($this->once())->method('isValid')->willReturn(true);
        $this->_checkoutSession->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $this->assertEquals($orderId, $this->_model->getOrderIdForPaymentStart());
    }
    
    public function testGetOrderIdForPaymentStartSuccessExisitingOrder()
    {
        $orderId = 1;
        $this->_checkoutSuccessValidator->expects($this->once())->method('isValid')->willReturn(false);
        $this->_request->expects($this->once())->method('getParam')->with($this->equalTo('id'))->willReturn($orderId);
        $this->assertEquals($orderId, $this->_model->getOrderIdForPaymentStart());
    }

    public function testGetOrderIdForPaymentStartFail()
    {
        $this->_checkoutSuccessValidator->expects($this->once())->method('isValid')->willReturn(false);
        $this->_request->expects($this->once())->method('getParam')->with($this->equalTo('id'))->willReturn(null);
        $this->assertFalse($this->_model->getOrderIdForPaymentStart());
    }

    public function testCanStartFirstPaymentFailInvalidCustomer()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_model->canStartFirstPayment($order));
    }

    public function testCanStartFirstPaymentFailAlreadyStarted()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_model->canStartFirstPayment($order));
    }

    public function testCanStartFirstPaymentFailInvalidPaymentMethod()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_model->canStartFirstPayment($order));
    }

    public function testCanStartFirstPaymentFailInvalidOrderState()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_model->canStartFirstPayment($order));
    }

    public function testCanStartFirstPaymentSuccess()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))->willReturn(true);
        $this->assertTrue($this->_model->canStartFirstPayment($order));
    }

    public function testCanRepeatPaymentFailInvalidCustomer()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentFailInvalidPaymentMethod()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentFailInvalidOrderState()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentFailAlreadyPaid()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNotPaid')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentFailNoTransaction()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNotPaid')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))->willReturn(true);
        $this->assertFalse($this->_model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentSuccess()
    {
        $order = $this->_getOrderMock();
        $this->_orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNotPaid')->with($this->equalTo($order))->willReturn(true);
        $this->_orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))->willReturn(false);
        $this->assertTrue($this->_model->canRepeatPayment($order));
    }

    public function testPaymentSuccessCheckFail()
    {
        $this->_request->expects($this->once())->method('getParam')->with($this->equalTo('exception'))->willReturn('1');
        $this->assertFalse($this->_model->paymentSuccessCheck());
    }

    public function testPaymentSuccessCheckSuccess()
    {
        $this->_request->expects($this->once())->method('getParam')->with($this->equalTo('exception'))->willReturn(null);
        $this->assertTrue($this->_model->paymentSuccessCheck());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Transaction::class)
            ->setMethods([
                'getId',
                'getPayuplOrderId',
                'getNewerId',
                'getOrderId',
                'getTry',
                'getStatus',
                'setOrderId',
                'setPayuplOrderId',
                'setPayuplExternalOrderId',
                'setTry',
                'setStatus',
                'save'
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMockForCanStartFirstPaymentWithCorrectPaymentMethodAndWithoutTransaction()
    {
        $order = $this->_getOrderMock();
        $orderId = 1;
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->_transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->with($this->equalTo($orderId))->willReturn(false);
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $payment->expects($this->once())->method('getMethod')->willReturn(Payupl::CODE);
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        return $order;
    }
}