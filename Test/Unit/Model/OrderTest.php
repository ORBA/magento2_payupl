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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionFactory;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_orderFactory = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_transactionFactory = $this->getMockBuilder(TransactionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_dataGetter = $this->getMockBuilder(Order\DataGetter::class)->disableOriginalConstructor()->getMock();
        $this->_model = $this->_objectManager->getObject(Order::class, [
            'orderFactory' => $this->_orderFactory,
            'dataGetter' => $this->_dataGetter,
            'transactionFactory' => $this->_transactionFactory
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

    public function testSaveNewTransaction()
    {
        $orderId = '1';
        $payuplOrderId = 'Z963D5JQR2230925GUEST000P01';
        $payuplExternalOrderId = '0000000001:1';
        $transaction = $this->getMockBuilder(Transaction::class)->setMethods([
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
        $transaction->expects($this->once())->method('setStatus')->with($this->equalTo('NEW'))->will($this->returnSelf());
        $transaction->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_transactionFactory->expects($this->once())->method('create')->willReturn($transaction);
        $this->_model->saveNewTransaction($orderId, $payuplOrderId, $payuplExternalOrderId);
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