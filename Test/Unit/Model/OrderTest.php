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

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_transactionCollectionFactory = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction\CollectionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_model = $this->getMockForAbstractClass(Order::class, [
            'transactionCollectionFactory' => $this->_transactionCollectionFactory
        ]);
    }

    public function testGetLastPayuplOrderIdByOrderIdFail()
    {
        $orderId = 1;
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditionsForGetLastPayuplOrderId($orderId);
        $transaction = $this->_getTransactionMockForGetLastPayuplOrderId();
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
        $transaction = $this->_getTransactionMockForGetLastPayuplOrderId();
        $transaction->expects($this->once())->method('getId')->willReturn($orderId);
        $transaction->expects($this->once())->method('getPayuplOrderId')->willReturn($payuplOrderId);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertEquals($payuplOrderId, $this->_model->getLastPayuplOrderIdByOrderId($orderId));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionMockForGetLastPayuplOrderId()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Transaction::class)->setMethods(['getId', 'getPayuplOrderId'])->disableOriginalConstructor()->getMock();
    }

    /**
     * @param $orderId
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

}