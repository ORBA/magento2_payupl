<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

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
    protected $_transactionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_transactionFactory = $this->getMockBuilder(\Orba\Payupl\Model\TransactionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_orderFactory = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $this->_model = $this->getMockForAbstractClass(Order::class, [
            'transactionFactory' => $this->_transactionFactory,
            'orderFactory' => $this->_orderFactory,
            'scopeConfig' => $this->_scopeConfig
        ]);
    }

    public function testSaveNewTransaction()
    {
        $orderId = '1';
        $payuplOrderId = 'Z963D5JQR2230925GUEST000P01';
        $payuplExternalOrderId = '0000000001:1';
        $transaction = $this->getMockBuilder(\Orba\Payupl\Model\Transaction::class)->setMethods([
            'setOrderId',
            'setPayuplOrderId',
            'setPayuplExternalOrderId',
            'setTry',
            'setStatus',
            'save'
        ])->disableOriginalConstructor()->getMock();
        $transaction->expects($this->once())->method('setOrderId')->with($this->equalTo($orderId))->will($this->returnSelf());
        $transaction->expects($this->once())->method('setPayuplOrderId')->with($this->equalTo($payuplOrderId))->will($this->returnSelf());
        $transaction->expects($this->once())->method('setPayuplExternalOrderId')->with($this->equalTo($payuplExternalOrderId))->will($this->returnSelf());
        $transaction->expects($this->once())->method('setTry')->with($this->equalTo(1))->will($this->returnSelf());
        $transaction->expects($this->once())->method('setStatus')->with($this->equalTo($this->_model->getNewStatus()))->will($this->returnSelf());
        $transaction->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_transactionFactory->expects($this->once())->method('create')->willReturn($transaction);
        $this->_model->saveNewTransaction($orderId, $payuplOrderId, $payuplExternalOrderId);
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
    
    public function testSetNewOrderStatus()
    {
        $order = $this->_getOrderMock();
        $order->expects($this->once())->method('setState')->with($this->equalTo(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT))->will($this->returnSelf());
        $orderStatus = 'pending_payment';
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->with($this->equalTo(Order::XML_PATH_ORDER_STATUS_NEW))->willReturn($orderStatus);
        $order->expects($this->once())->method('addStatusToHistory')->with($this->equalTo($orderStatus))->will($this->returnSelf());
        $order->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_model->setNewOrderStatus($order);
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock()
    {
        return $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
    }

}