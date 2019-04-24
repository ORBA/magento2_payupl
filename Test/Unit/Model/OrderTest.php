<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Order
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionCollectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSuccessValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderValidator;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->transactionResource = $this->getMockBuilder(ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderFactory = $this->getMockBuilder(\Orba\Payupl\Model\Sales\OrderFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->checkoutSuccessValidator = $this->getMockBuilder(\Magento\Checkout\Model\Session\SuccessValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()->setMethods(['getLastOrderId'])->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();
        $this->orderValidator = $this->getMockBuilder(Order\Validator::class)->disableOriginalConstructor()->getMock();
        $this->model = $this->getMockForAbstractClass(Order::class, [
            'transactionResource' => $this->transactionResource,
            'orderFactory' => $this->orderFactory,
            'checkoutSuccessValidator' => $this->checkoutSuccessValidator,
            'checkoutSession' => $this->checkoutSession,
            'request' => $this->request,
            'orderValidator' => $this->orderValidator
        ]);
    }

    public function testGetOrderByIdFailNotFound()
    {
        $orderId = 1;
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('load')->with($this->equalTo($orderId))->will($this->returnSelf());
        $order->expects($this->once())->method('getId')->willReturn(false);
        $this->orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->assertFalse($this->model->loadOrderById($orderId));
    }

    public function testGetOrderByIdSuccess()
    {
        $orderId = 1;
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('load')->with($this->equalTo($orderId))->will($this->returnSelf());
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->assertEquals($order, $this->model->loadOrderById($orderId));
    }

    public function testGetOrderByPayuplOrderIdFailNotFound()
    {
        $payuplOrderId = 'ABC';
        $this->transactionResource->expects($this->once())->method('getOrderIdByPayuplOrderId')
            ->with($this->equalTo($payuplOrderId))->willReturn(false);
        $this->assertFalse($this->model->loadOrderByPayuplOrderId($payuplOrderId));
    }

    public function testGetOrderByPayuplOrderIdSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'ABC';
        $this->transactionResource->expects($this->once())->method('getOrderIdByPayuplOrderId')
            ->with($this->equalTo($payuplOrderId))->willReturn($orderId);
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('load')->with($this->equalTo($orderId))->will($this->returnSelf());
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->assertEquals($order, $this->model->loadOrderByPayuplOrderId($payuplOrderId));
    }

    public function testSetNewOrderStatus()
    {
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('setState')
            ->with($this->equalTo(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT))->will($this->returnSelf());
        $order->expects($this->once())->method('addStatusToHistory')->with($this->equalTo(true))
            ->will($this->returnSelf());
        $order->expects($this->once())->method('save')->will($this->returnSelf());
        $this->model->setNewOrderStatus($order);
    }

    public function testSetHoldedOrderStatus()
    {
        $status = 'REJECTED';
        $orderStateOld = 'old_state';
        $orderStatusOld = 'old_status';
        $orderStateNew = Sales\Order::STATE_HOLDED;
        $orderStatusNew = 'new_status';
        $order = $this->getOrderMock();
        $config = $this->getMockBuilder(Sales\Order\Config::class)->disableOriginalConstructor()->getMock();
        $config->expects($this->once())->method('getStateDefaultStatus')->with($this->equalTo($orderStateNew))
            ->willReturn($orderStatusNew);
        $order->expects($this->once())->method('getConfig')->willReturn($config);
        $order->expects($this->once())->method('getState')->willReturn($orderStateOld);
        $order->expects($this->once())->method('getStatus')->willReturn($orderStatusOld);
        $order->expects($this->once())->method('setHoldBeforeState')->with($this->equalTo($orderStateOld))
            ->will($this->returnSelf());
        $order->expects($this->once())->method('setHoldBeforeStatus')->with($this->equalTo($orderStatusOld))
            ->will($this->returnSelf());
        $order->expects($this->once())->method('setState')->with($this->equalTo($orderStateNew))
            ->will($this->returnSelf());
        $order->expects($this->once())->method('setStatus')->with($this->equalTo($orderStatusNew))
            ->will($this->returnSelf());
        $order->expects($this->once())->method('addStatusHistoryComment')
            ->with($this->equalTo(__('Payu.pl status') . ': ' . $status));
        $order->expects($this->once())->method('save')->will($this->returnSelf());
        $this->model->setHoldedOrderStatus($order, $status);
    }

    public function testCompletePayment()
    {
        $amount = 2.22;
        $payuplOrderId = 'ABC';
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->setMethods([
            'setParentTransactionId',
            'setTransactionId',
            'registerCaptureNotification',
            'save'
        ])->disableOriginalConstructor()->getMock();
        $payment->expects($this->once())->method('setParentTransactionId')->with($payuplOrderId)
            ->will($this->returnSelf());
        $payment->expects($this->once())->method('setTransactionId')->with($payuplOrderId . ':C')
            ->will($this->returnSelf());
        $payment->expects($this->once())->method('registerCaptureNotification')->with($this->equalTo($amount))
            ->will($this->returnSelf());
        $payment->expects($this->once())->method('save');
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $invoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)->disableOriginalConstructor()
            ->getMock();
        $invoice->expects($this->once())->method('save');
        $order->expects($this->once())->method('getRelatedObjects')->willReturn([$invoice]);
        $order->expects($this->once())->method('save')->will($this->returnSelf());
        $this->model->completePayment($order, $amount, $payuplOrderId);
    }

    public function testGetOrderIdForPaymentStartSuccessNewOrder()
    {
        $orderId = 1;
        $this->checkoutSuccessValidator->expects($this->once())->method('isValid')->willReturn(true);
        $this->checkoutSession->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $this->assertEquals($orderId, $this->model->getOrderIdForPaymentStart());
    }
    
    public function testGetOrderIdForPaymentStartSuccessExisitingOrder()
    {
        $orderId = 1;
        $this->checkoutSuccessValidator->expects($this->once())->method('isValid')->willReturn(false);
        $this->request->expects($this->once())->method('getParam')->with($this->equalTo('id'))->willReturn($orderId);
        $this->assertEquals($orderId, $this->model->getOrderIdForPaymentStart());
    }

    public function testGetOrderIdForPaymentStartFail()
    {
        $this->checkoutSuccessValidator->expects($this->once())->method('isValid')->willReturn(false);
        $this->request->expects($this->once())->method('getParam')->with($this->equalTo('id'))->willReturn(null);
        $this->assertFalse($this->model->getOrderIdForPaymentStart());
    }

    public function testCanStartFirstPaymentFailInvalidCustomer()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->model->canStartFirstPayment($order));
    }

    public function testCanStartFirstPaymentFailAlreadyStarted()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->model->canStartFirstPayment($order));
    }

    public function testCanStartFirstPaymentFailInvalidPaymentMethod()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->model->canStartFirstPayment($order));
    }

    public function testCanStartFirstPaymentFailInvalidOrderState()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->model->canStartFirstPayment($order));
    }

    public function testCanStartFirstPaymentSuccess()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))
            ->willReturn(true);
        $this->assertTrue($this->model->canStartFirstPayment($order));
    }

    public function testCanRepeatPaymentFailInvalidCustomer()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentFailInvalidPaymentMethod()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentFailInvalidOrderState()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentFailAlreadyPaid()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNotPaid')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentFailNoTransaction()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNotPaid')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))
            ->willReturn(true);
        $this->assertFalse($this->model->canRepeatPayment($order));
    }

    public function testCanRepeatPaymentSuccess()
    {
        $order = $this->getOrderMock();
        $this->orderValidator->expects($this->once())->method('validateCustomer')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validatePaymentMethod')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateState')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNotPaid')->with($this->equalTo($order))
            ->willReturn(true);
        $this->orderValidator->expects($this->once())->method('validateNoTransactions')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertTrue($this->model->canRepeatPayment($order));
    }

    public function testPaymentSuccessCheckFail()
    {
        $this->request->expects($this->once())->method('getParam')->with($this->equalTo('exception'))->willReturn('1');
        $this->assertFalse($this->model->paymentSuccessCheck());
    }

    public function testPaymentSuccessCheckSuccess()
    {
        $this->request->expects($this->once())->method('getParam')->with($this->equalTo('exception'))->willReturn(null);
        $this->assertTrue($this->model->paymentSuccessCheck());
    }

    public function testAddNewOrderTransaction()
    {
        $payuplOrderId = 'ABC';
        $status = 'NEW';
        $orderId = 1;
        $try = 2;
        $order = $this->getOrderMock();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->transactionResource->expects($this->once())->method('getLastTryByOrderId')->willReturn($try);
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->once())->method('setTransactionId')->with($this->equalTo($payuplOrderId));
        $payment->expects($this->once())->method('setTransactionAdditionalInfo')->with(
            $this->equalTo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS),
            $this->equalTo([
                'try' => $try + 1,
                'status' => $status
            ])
        );
        $payment->expects($this->once())->method('setIsTransactionClosed')->with($this->equalTo(0));
        $transaction = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $transaction->expects($this->once())->method('save');
        $payment->expects($this->once())->method('addTransaction')->with($this->equalTo('order'))
            ->willReturn($transaction);
        $payment->expects($this->once())->method('save');
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $this->model->addNewOrderTransaction($order, $payuplOrderId, $status);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMockForCanStartFirstPaymentWithCorrectPaymentMethodAndWithoutTransaction()
    {
        $order = $this->getOrderMock();
        $orderId = 1;
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')
            ->with($this->equalTo($orderId))->willReturn(false);
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->once())->method('getMethod')->willReturn(Payupl::CODE);
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        return $order;
    }
}
