<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class ExtOrderIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtOrderId
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(ExtOrderId::class, [
            'transactionResource' => $this->_transactionResource,
            'dateTime' => $this->_dateTime
        ]);
    }

    public function testGenerate()
    {
        $orderId = '1';
        $orderIncrementId = '0000000001';
        $timestamp = 12345678;
        $try = 2;
        $order = $this->_getOrderMock($orderId, $orderIncrementId);
        $this->_transactionResource->expects($this->once())->method('getLastTryByOrderId')->with($this->equalTo($orderId))->willReturn($try);
        $this->_dateTime->expects($this->once())->method('timestamp')->willReturn($timestamp);
        $this->assertEquals($orderIncrementId . ':' . $timestamp . ':' . ($try + 1), $this->_model->generate($order));
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Transaction::class)->setMethods(['getId', 'getTry'])->disableOriginalConstructor()->getMock();
    }
}