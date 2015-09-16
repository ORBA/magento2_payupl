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
    protected $_orderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataGetter;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_orderFactory = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_dataGetter = $this->getMockBuilder(Order\DataGetter::class)->getMock();
        $this->_model = $this->_objectManager->getObject(Order::class, [
            'orderFactory' => $this->_orderFactory,
            'dataGetter' => $this->_dataGetter
        ]);
    }

    public function testGetDataForNewTransactionFailOrderNotFound()
    {
        $orderId = '1';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->_expectOrderLoadById($order, $orderId);
        $order->expects($this->once())->method('getId')->willReturn(null);
        $this->_orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->setExpectedException(Order\Exception::class, 'Order with ID ' . $orderId . ' does not exist.');
        $this->_model->getDataForNewTransaction($orderId);
    }

    public function testGetDataForNewTransactionSuccessNoBuyer()
    {
        $productsData = ['products'];
        $shippingData = ['shipping'];
        $buyerData = null;
        $basicData = ['basic'];
        $this->_preTestGetDataForNewTransactionSuccess('1', $productsData, $shippingData, $buyerData, $basicData);
        $productsData[] = $shippingData;
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData]
            ),
            $this->_model->getDataForNewTransaction('1')
        );
    }

    public function testGetDataForNewTransactionSuccessNoShipping()
    {
        $productsData = ['products'];
        $shippingData = null;
        $buyerData = ['buyer'];
        $basicData = ['basic'];
        $this->_preTestGetDataForNewTransactionSuccess('1', $productsData, $shippingData, $buyerData, $basicData);
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData],
                ['buyer' => $buyerData]
            ),
            $this->_model->getDataForNewTransaction('1')
        );
    }

    public function testGetDataForNewTransactionSuccessAllData()
    {
        $productsData = ['products'];
        $shippingData = ['shipping'];
        $buyerData = ['buyer'];
        $basicData = ['basic'];
        $this->_preTestGetDataForNewTransactionSuccess('1', $productsData, $shippingData, $buyerData, $basicData);
        $productsData[] = $shippingData;
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData],
                ['buyer' => $buyerData]
            ),
            $this->_model->getDataForNewTransaction('1')
        );
    }

    /**
     * @param $order
     * @param $orderId
     */
    protected function _expectOrderLoadById($order, $orderId)
    {
        $order->expects($this->once())->method('load')->with($this->equalTo($orderId))->will($this->returnSelf());
    }

    /**
     * @param string $orderId
     * @param array $productsData
     * @param array|null $shippingData
     * @param array|null $buyerData
     * @param array $basicData
     */
    protected function _preTestGetDataForNewTransactionSuccess($orderId, array $productsData, $shippingData, $buyerData, array $basicData)
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->_expectOrderLoadById($order, $orderId);
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $this->_orderFactory->expects($this->once())->method('create')->willReturn($order);
        $this->_dataGetter->expects($this->once())->method('getProductsData')->willReturn($productsData);
        $this->_dataGetter->expects($this->once())->method('getShippingData')->willReturn($shippingData);
        $this->_dataGetter->expects($this->once())->method('getBuyerData')->willReturn($buyerData);
        $this->_dataGetter->expects($this->once())->method('getBasicData')->willReturn($basicData);
    }
}