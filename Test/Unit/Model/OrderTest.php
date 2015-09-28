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

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_transactionCollectionFactory = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction\CollectionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_transactionFactory = $this->getMockBuilder(\Orba\Payupl\Model\TransactionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_model = $this->getMockForAbstractClass(Order::class, [
            'transactionCollectionFactory' => $this->_transactionCollectionFactory,
            'transactionFactory' => $this->_transactionFactory
        ]);
    }

    public function testGetLastPayuplOrderIdByOrderIdFail()
    {
        $orderId = 1;
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForGetLastPayuplOrderId($orderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(null);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertFalse($this->_model->getLastPayuplOrderIdByOrderId($orderId));
    }

    public function testGetLastPayuplOrderIdByOrderIdSuccess()
    {
        $orderId = 1;
        $payuplOrderId = '123';
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForGetLastPayuplOrderId($orderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(1);
        $transaction->expects($this->once())->method('getPayuplOrderId')->willReturn($payuplOrderId);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertEquals($payuplOrderId, $this->_model->getLastPayuplOrderIdByOrderId($orderId));
    }

    public function testCheckIfNewestByPayuplOrderIdFailNotFound()
    {
        $payuplOrderId = '123';
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForCheckIfNewestByPayuplOrderId($payuplOrderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(null);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertFalse($this->_model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testCheckIfNewestByPayuplOrderIdFailOld()
    {
        $payuplOrderId = '123';
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForCheckIfNewestByPayuplOrderId($payuplOrderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(1);
        $transaction->expects($this->once())->method('getNewerId')->willReturn(2);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertFalse($this->_model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testCheckIfNewestByPayuplOrderIdSuccess()
    {
        $payuplOrderId = '123';
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForCheckIfNewestByPayuplOrderId($payuplOrderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(1);
        $transaction->expects($this->once())->method('getNewerId')->willReturn(null);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertTrue($this->_model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testGetOrderIdByPayuplOrderIdFail()
    {
        $payuplOrderId = 'ABC';
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForGetByPayuplOrderId($payuplOrderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(null);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertFalse($this->_model->getOrderIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetOrderIdByPayuplOrderIdSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'ABC';
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForGetByPayuplOrderId($payuplOrderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(1);
        $transaction->expects($this->once())->method('getOrderId')->willReturn($orderId);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertEquals($orderId, $this->_model->getOrderIdByPayuplOrderId($payuplOrderId));
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

    public function testGetStatusByPayuplOrderIdFail()
    {
        $payuplOrderId = 'ABC';
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForGetByPayuplOrderId($payuplOrderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(null);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertFalse($this->_model->getStatusByPayuplOrderId($payuplOrderId));
    }

    public function testGetStatusByPayuplOrderIdSuccess()
    {
        $status = 'COMPLETED';
        $payuplOrderId = 'ABC';
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForGetByPayuplOrderId($payuplOrderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(1);
        $transaction->expects($this->once())->method('getStatus')->willReturn($status);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertEquals($status, $this->_model->getStatusByPayuplOrderId($payuplOrderId));
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
     * @param int $orderId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionCollectionWithExpectedConditionsForGetLastPayuplOrderId($orderId)
    {
        $transactionCollection = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction\Collection::class)->disableOriginalConstructor()->getMock();
        $transactionCollection->expects($this->once())->method('addFieldToFilter')->with(
            $this->equalTo('order_id'),
            $this->equalTo($orderId)
        )->will($this->returnSelf());
        $transactionCollection->expects($this->once())->method('setOrder')->with(
            $this->equalTo('try'),
            $this->equalTo(\Magento\Framework\Data\Collection::SORT_ORDER_DESC)
        )->will($this->returnSelf());
        return $transactionCollection;
    }

    /**
     * @param string $payuplOrderId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionCollectionWithExpectedConditionsForCheckIfNewestByPayuplOrderId($payuplOrderId)
    {
        $transactionCollection = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction\Collection::class)->disableOriginalConstructor()->getMock();
        $transactionCollection->expects($this->once())->method('addFieldToFilter')->with(
            $this->equalTo('main_table.payupl_order_id'),
            $this->equalTo($payuplOrderId)
        )->will($this->returnSelf());
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)->disableOriginalConstructor()->getMock();
        $select->expects($this->once())->method('joinLeft')->with(
            $this->equalTo(['t2' => 'orba_payupl_transaction']),
            $this->equalTo('t2.order_id = main_table.order_id AND t2.try > main_table.try'),
            $this->equalTo(['newer_id' => 't2.order_id'])
        );
        $transactionCollection->expects($this->once())->method('getSelect')->willReturn($select);
        return $transactionCollection;
    }

    /**
     * @param string $payuplOrderId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionCollectionWithExpectedConditionsForGetByPayuplOrderId($payuplOrderId)
    {
        $transactionCollection = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction\Collection::class)->disableOriginalConstructor()->getMock();
        $transactionCollection->expects($this->once())->method('addFieldToFilter')->with(
            $this->equalTo('payupl_order_id'),
            $this->equalTo($payuplOrderId)
        )->will($this->returnSelf());
        return $transactionCollection;
    }

}