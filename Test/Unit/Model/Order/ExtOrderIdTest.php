<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class ExtOrderIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ExtOrderId
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(ExtOrderId::class, [
            'transactionResource' => $this->transactionResource,
            'dateTime' => $this->dateTime
        ]);
    }

    public function testGenerate()
    {
        $orderId = '1';
        $orderIncrementId = '0000000001';
        $timestamp = 12345678;
        $try = 2;
        $order = $this->getOrderMock($orderId, $orderIncrementId);
        $this->transactionResource->expects($this->once())->method('getLastTryByOrderId')
            ->with($this->equalTo($orderId))->willReturn($try);
        $this->dateTime->expects($this->once())->method('timestamp')->willReturn($timestamp);
        $this->assertEquals($orderIncrementId . ':' . $timestamp . ':' . ($try + 1), $this->model->generate($order));
    }

    /**
     * @param $orderId
     * @param $orderIncrementId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock($orderId, $orderIncrementId)
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $order->expects($this->once())->method('getIncrementId')->willReturn($orderIncrementId);
        return $order;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTransactionMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Transaction::class)->setMethods(['getId', 'getTry'])
            ->disableOriginalConstructor()->getMock();
    }
}
