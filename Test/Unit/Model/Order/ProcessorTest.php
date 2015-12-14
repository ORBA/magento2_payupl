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
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionService;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()
            ->getMock();
        $this->transactionService = $this->getMockBuilder(\Orba\Payupl\Model\Transaction\Service::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(Processor::class, [
            'orderHelper' => $this->orderHelper,
            'transactionService' => $this->transactionService
        ]);
    }

    public function testProcessOldSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'COMPLETED';
        $close = true;
        $this->transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo($close)
        );
        $this->model->processOld($payuplOrderId, $status, $close);
    }

    public function testProcessPendingSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'PENDING';
        $this->transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(false)
        );
        $this->model->processPending($payuplOrderId, $status);
    }

    public function testProcessHoldedFailOrderNotFound()
    {
        $this->expectOrderNotFoundException();
        $this->model->processHolded('ABC', 'REJECTED');
    }

    public function testProcessHoldedSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'WAITING';
        $order = $this->getOrderMock();
        $this->orderHelper->expects($this->once())->method('loadOrderByPayuplOrderId')->willReturn($order);
        $this->orderHelper->expects($this->once())->method('setHoldedOrderStatus')->with(
            $this->equalTo($order),
            $this->equalTo($status)
        );
        $this->transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(true)
        );
        $this->model->processHolded($payuplOrderId, $status);
    }

    public function testProcessWaitingSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'WAITING';
        $this->transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(false)
        );
        $this->model->processWaiting($payuplOrderId, $status);
    }

    public function testProcessCompletedFailOrderNotFound()
    {
        $this->expectOrderNotFoundException();
        $this->model->processCompleted('ABC', 'COMPLETED', 2.22);
    }

    public function testProcessCompletedSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'COMPLETED';
        $amount = 2.22;
        $order = $this->getOrderMock();
        $this->orderHelper->expects($this->once())->method('loadOrderByPayuplOrderId')->willReturn($order);
        $this->orderHelper->expects($this->once())->method('completePayment')->with(
            $this->equalTo($order),
            $this->equalTo($amount)
        );
        $this->transactionService->expects($this->once())->method('updateStatus')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(true)
        );
        $this->model->processCompleted($payuplOrderId, $status, $amount);
    }

    protected function expectOrderNotFoundException()
    {
        $this->orderHelper->expects($this->once())->method('loadOrderByPayuplOrderId')->willReturn(false);
        $this->setExpectedException(Exception::class, 'Order not found.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
    }
}
