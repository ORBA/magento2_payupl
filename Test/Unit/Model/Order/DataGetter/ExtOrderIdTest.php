<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order\DataGetter;

class ExtOrderIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtOrderId
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionCollectionFactory;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_transactionCollectionFactory = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction\CollectionFactory::class)->setMethods(['create'])->getMock();
        $this->_model = $objectManager->getObject(ExtOrderId::class, [
            'transactionCollectionFactory' => $this->_transactionCollectionFactory
        ]);
    }

    public function testGenerateFirst()
    {
        $orderId = '1';
        $orderIncrementId = '0000000001';
        $order = $this->_getOrderMock($orderId, $orderIncrementId);
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditions($orderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn(null);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertEquals($orderIncrementId . ':1', $this->_model->generate($order));
    }

    public function testGenerateNth()
    {
        $orderId = '1';
        $orderIncrementId = '0000000001';
        $try = 10;
        $order = $this->_getOrderMock($orderId, $orderIncrementId);
        $transactionCollection = $this->_getTransactionCollectionWithExpectedConditions($orderId);
        $transaction = $this->_getTransactionMock();
        $transaction->expects($this->once())->method('getId')->willReturn($orderId);
        $transaction->expects($this->once())->method('getTry')->willReturn($try);
        $transactionCollection->expects($this->once())->method('getFirstItem')->willReturn($transaction);
        $this->_transactionCollectionFactory->expects($this->once())->method('create')->willReturn($transactionCollection);
        $this->assertEquals($orderIncrementId . ':' . ($try + 1), $this->_model->generate($order));
    }

    /**
     * @param $orderId
     * @param $orderIncrementId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock($orderId, $orderIncrementId)
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $order->expects($this->once())->method('getIncrementId')->willReturn($orderIncrementId);
        return $order;
    }

    /**
     * @param $orderId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionCollectionWithExpectedConditions($orderId)
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Transaction::class)->setMethods(['getId', 'getTry'])->disableOriginalConstructor()->getMock();
    }
}