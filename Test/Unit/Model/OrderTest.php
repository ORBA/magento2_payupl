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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderFactory;

    /**
     * @var Order
     */
    protected $_model;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_orderFactory = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_model = $this->_objectManager->getObject(Order::class, [
            'orderFactory' => $this->_orderFactory
        ]);
    }

    public function testGetDataForNewTransactionFailOrderNotFound()
    {
        $orderId = '1';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->setMethods([
            'load',
            'getId'
        ])->disableOriginalConstructor()->getMock();
        $this->_expectOrderLoadById($order, $orderId);
        $order->expects($this->once())->method('getId')->willReturn(null);
        $this->_orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->setExpectedException(Order\Exception::class, 'Order with ID ' . $orderId . ' does not exist.');
        $this->_model->getDataForNewTransaction($orderId);
    }

    public function testGetDataForNewTransactionSuccess()
    {
        $incrementId = '0000000001';
        $id = '1';
        $currency = 'PLN';
        $amount = '10.9800';
        $description = __('Order # %1', [$incrementId]);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->setMethods([
            'load',
            'getId',
            'getIncrementId',
            'getOrderCurrencyCode',
            'getGrandTotal',
            'getAllVisibleItems'
        ])->disableOriginalConstructor()->getMock();
        $this->_expectOrderLoadById($order, $incrementId);
        $order->expects($this->once())->method('getId')->willReturn($id);
        $order->expects($this->once())->method('getIncrementId')->willReturn($incrementId);
        $order->expects($this->once())->method('getOrderCurrencyCode')->willReturn($currency);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($amount);
        $name = 'Example';
        $price = '5.4900';
        $quantity = '1.5';
        $orderItem = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)->setMethods([
            'getName',
            'getPriceInclTax',
            'getQtyOrdered'
        ])->disableOriginalConstructor()->getMock();
        $orderItem->expects($this->once())->method('getName')->willReturn($name);
        $orderItem->expects($this->once())->method('getPriceInclTax')->willReturn($price);
        $orderItem->expects($this->once())->method('getQtyOrdered')->willReturn($quantity);
        $orderItems = [
            $orderItem
        ];
        $order->expects($this->once())->method('getAllVisibleItems')->willReturn($orderItems);
        $this->_orderFactory->expects($this->once())->method('create')->willReturn($order);
        $dataForNewTransaction = $this->_model->getDataForNewTransaction($incrementId);
        $this->assertEquals([
            'currencyCode' => $currency,
            'totalAmount' => $amount * 100,
            'extOrderId' => $incrementId,
            'description' => $description,
            'products' => [
                [
                    'name' => $name,
                    'unitPrice' => $price * 100,
                    'quantity' => $quantity
                ]
            ]
        ], $dataForNewTransaction);
        $this->assertInternalType('float', $dataForNewTransaction['products'][0]['quantity']);
    }

    /**
     * @param $order
     * @param $orderId
     */
    protected function _expectOrderLoadById($order, $orderId)
    {
        $order->expects($this->once())->method('load')->with($this->equalTo($orderId))->will($this->returnSelf());
    }
}