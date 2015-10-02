<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionService;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->_transactionService = $this->getMockBuilder(\Orba\Payupl\Model\Transaction\Service::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Processor::class, [
            'orderHelper' => $this->_orderHelper,
            'transactionService' => $this->_transactionService
        ]);
    }

    public function testProcessOldSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'COMPLETED';
        $this->_transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status)
        );
        $this->_model->processOld($payuplOrderId, $status);
    }

    public function testProcessPendingSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'PENDING';
        $this->_transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status)
        );
        $this->_model->processPending($payuplOrderId, $status);
    }

    public function testProcessHoldedFailOrderNotFound()
    {
        $this->_expectOrderNotFoundException();
        $this->_model->processHolded('ABC', 'REJECTED');
    }

    public function testProcessHoldedSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'WAITING';
        $order = $this->_getOrderMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderByPayuplOrderId')->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('setHoldedOrderStatus')->with(
            $this->equalTo($order),
            $this->equalTo($status)
        );
        $this->_transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status)
        );
        $this->_model->processHolded($payuplOrderId, $status);
    }

    public function testProcessWaitingSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'WAITING';
        $this->_transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status)
        );
        $this->_model->processWaiting($payuplOrderId, $status);
    }

    public function testProcessCompletedFailOrderNotFound()
    {
        $this->_expectOrderNotFoundException();
        $this->_model->processCompleted('ABC', 'COMPLETED', 2.22);
    }

    public function testProcessCompletedSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'COMPLETED';
        $amount = 2.22;
        $order = $this->_getOrderMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderByPayuplOrderId')->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('completePayment')->with(
            $this->equalTo($order),
            $this->equalTo($amount)
        );
        $this->_transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status)
        );
        $this->_model->processCompleted($payuplOrderId, $status, $amount);
    }

    protected function _expectOrderNotFoundException()
    {
        $this->_orderHelper->expects($this->once())->method('loadOrderByPayuplOrderId')->willReturn(false);
        $this->setExpectedException(Exception::class, 'Order not found.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
    }
}