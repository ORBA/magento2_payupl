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

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_transactionFactory = $this->getMockBuilder(\Orba\Payupl\Model\TransactionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_model = $this->getMockForAbstractClass(Order::class, [
            'transactionFactory' => $this->_transactionFactory
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

}