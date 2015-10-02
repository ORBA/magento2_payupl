<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Transaction;

use Orba\Payupl\Model\Transaction;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Service
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionFactory;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_transactionFactory = $this->getMockBuilder(\Orba\Payupl\Model\TransactionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Service::class, [
            'transactionFactory' => $this->_transactionFactory
        ]);
    }

    public function testUpdateStatusFailNotFound()
    {
        $payuplOrderId = 'ABC';
        $transaction = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $transaction->expects($this->once())->method('load')->with(
            $this->equalTo($payuplOrderId),
            'payupl_order_id'
        )->will($this->returnSelf());
        $transaction->expects($this->once())->method('getId')->willReturn(null);
        $this->_transactionFactory->expects($this->once())->method('create')->willReturn($transaction);
        $this->setExpectedException(Exception::class, 'Transaction not found.');
        $this->_model->updateStatus($payuplOrderId, 'COMPLETED');
    }

    public function testUpdateStatusSuccess()
    {
        $payuplOrderId = 'ABC';
        $orderId = 1;
        $status = 'COMPLETED';
        $transaction = $this->getMockBuilder(Transaction::class)->setMethods(['load', 'getId', 'setStatus', 'save'])->disableOriginalConstructor()->getMock();
        $transaction->expects($this->once())->method('load')->with(
            $this->equalTo($payuplOrderId),
            'payupl_order_id'
        )->will($this->returnSelf());
        $transaction->expects($this->once())->method('getId')->willReturn($orderId);
        $transaction->expects($this->once())->method('setStatus')->with($this->equalTo($status))->will($this->returnSelf());
        $transaction->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_transactionFactory->expects($this->once())->method('create')->willReturn($transaction);
        $this->_model->updateStatus($payuplOrderId, $status);
    }
}