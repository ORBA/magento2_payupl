<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\Order;

use Magento\Framework\Exception\LocalizedException;
use Orba\Payupl\Model\Client\Rest\Order;

class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Processor
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderProcessor;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderProcessor = $this->getMockBuilder(\Orba\Payupl\Model\Order\Processor::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(Processor::class, [
            'orderProcessor' => $this->orderProcessor
        ]);
    }

    public function testProcessStatusChangeInvalid()
    {
        $this->expectException(LocalizedException::class, 'Invalid status.');
        $this->model->processStatusChange(1, 'INVALID STATUS');
    }

    public function testProcessStatusChangeNewNotNewest()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_NEW;
        $this->orderProcessor->expects($this->once())->method('processOld')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(false)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($payuplOrderId, $status, 2.22, false));
    }

    public function testProcessStatusChangePendingNotNewest()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_PENDING;
        $this->orderProcessor->expects($this->once())->method('processOld')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(false)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($payuplOrderId, $status, 2.22, false));
    }

    public function testProcessStatusChangeCancelledNotNewest()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_CANCELLED;
        $this->orderProcessor->expects($this->once())->method('processOld')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(true)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($payuplOrderId, $status, 2.22, false));
    }

    public function testProcessStatusChangeRejectedNotNewest()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_REJECTED;
        $this->orderProcessor->expects($this->once())->method('processOld')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(true)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($payuplOrderId, $status, 2.22, false));
    }

    public function testProcessStatusChangeWaitingNotNewest()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_WAITING;
        $this->orderProcessor->expects($this->once())->method('processOld')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(false)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($payuplOrderId, $status, 2.22, false));
    }

    public function testProcessStatusChangeCompletedNotNewest()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_COMPLETED;
        $this->orderProcessor->expects($this->once())->method('processOld')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo(true)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($payuplOrderId, $status, 2.22, false));
    }

    public function testProcessStatusChangeNew()
    {
        $orderId = 1;
        $status = Order::STATUS_NEW;
        $this->orderProcessor->expects($this->once())->method('processPending')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangePending()
    {
        $orderId = 1;
        $status = Order::STATUS_PENDING;
        $this->orderProcessor->expects($this->once())->method('processPending')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangeCancelled()
    {
        $orderId = 1;
        $status = Order::STATUS_CANCELLED;
        $this->orderProcessor->expects($this->once())->method('processHolded')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangeRejected()
    {
        $orderId = 1;
        $status = Order::STATUS_REJECTED;
        $this->orderProcessor->expects($this->once())->method('processHolded')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangeWaiting()
    {
        $orderId = 1;
        $status = Order::STATUS_WAITING;
        $this->orderProcessor->expects($this->once())->method('processWaiting')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($orderId, $status));
    }

    public function testProcessStatusChangeCompleted()
    {
        $orderId = 1;
        $status = Order::STATUS_COMPLETED;
        $this->orderProcessor->expects($this->once())->method('processCompleted')->with(
            $this->equalTo($orderId),
            $this->equalTo($status)
        )->willReturn(true);
        $this->assertTrue($this->model->processStatusChange($orderId, $status));
    }
}
