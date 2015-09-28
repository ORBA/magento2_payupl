<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\Order;

use \Orba\Payupl\Model\Client\Exception;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderProcessor;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_orderProcessor = $this->getMockBuilder(\Orba\Payupl\Model\Order\Processor::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Processor::class, [
            'orderProcessor' => $this->_orderProcessor
        ]);
    }

    public function testProcessStatusChangeInvalid()
    {
        $this->setExpectedException(Exception::class, 'Invalid status.');
        $this->_model->processStatusChange(1, 'INVALID STATUS');
    }

    public function testProcessStatusChangePending()
    {
        $statuses = [
            'NEW',
            'PENDING'
        ];
        foreach ($statuses as $status) {
            $this->assertTrue($this->_model->processStatusChange(1, $status));
        }
    }

    public function testProcessStatusChangeCancelled()
    {
        $orderId = 1;
        $isNewest = true;
        $this->_orderProcessor->expects($this->once())->method('processHold')->with(
            $this->equalTo($orderId),
            $this->equalTo($isNewest)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, 'CANCELLED'));
    }

    public function testProcessStatusChangeRejected()
    {
        $orderId = 1;
        $isNewest = true;
        $this->_orderProcessor->expects($this->once())->method('processHold')->with(
            $this->equalTo($orderId),
            $this->equalTo($isNewest)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, 'REJECTED'));
    }

    public function testProcessStatusChangeWaiting()
    {
        $orderId = 1;
        $isNewest = true;
        $this->_orderProcessor->expects($this->once())->method('processWaiting')->with(
            $this->equalTo($orderId),
            $this->equalTo($isNewest)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, 'WAITING_FOR_CONFIRMATION'));
    }

    public function testProcessStatusChangeCompleted()
    {
        $orderId = 1;
        $isNewest = true;
        $this->_orderProcessor->expects($this->once())->method('processCompleted')->with(
            $this->equalTo($orderId),
            $this->equalTo($isNewest)
        )->willReturn(true);
        $this->assertTrue($this->_model->processStatusChange($orderId, 'COMPLETED'));
    }
}