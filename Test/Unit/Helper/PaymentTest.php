<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Helper;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payment
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderHelper;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();
        $this->_orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()->getMock();
        $context = $objectManager->getObject(
            \Magento\Framework\App\Helper\Context::class,
            ['urlBuilder' => $this->_urlBuilder]
        );
        $this->_helper = $objectManager->getObject(Payment::class, [
            'context' => $context,
            'transactionResource' => $this->_transactionResource,
            'orderHelper' => $this->_orderHelper
        ]);
    }

    public function testGetStartPaymentUrlFailOrderNotFound()
    {
        $orderId = 1;
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn(false);
        $this->assertFalse($this->_helper->getStartPaymentUrl($orderId));
    }

    public function testGetStartPaymentUrlFailInvalidOrder()
    {
        $orderId = 1;
        $order = $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_helper->getStartPaymentUrl($orderId));
    }

    public function testGetStartPaymentUrlSuccess()
    {
        $orderId = 1;
        $order = $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))->willReturn(true);
        $path = 'orba_payupl/payment/start';
        $params = ['id' => $orderId];
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path . '/id/' . $orderId;
        $this->_urlBuilder->expects($this->once())->method('getUrl')->with(
            $this->equalTo($path),
            $this->equalTo($params)
        )->willReturn($url);
        $this->assertEquals($url, $this->_helper->getStartPaymentUrl($orderId));
    }

    public function testGetRepeatPaymentUrlFailOrderNotFound()
    {
        $orderId = 1;
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn(false);
        $this->assertFalse($this->_helper->getRepeatPaymentUrl($orderId));
    }

    public function testGetRepeatPaymentUrlFailInvalidOrder()
    {
        $orderId = 1;
        $order = $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('canRepeatPayment')->with($this->equalTo($order))->willReturn(false);
        $this->assertFalse($this->_helper->getRepeatPaymentUrl($orderId));
    }

    public function testGetRepeatPaymentUrlSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'ABC';
        $order = $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('canRepeatPayment')->with($this->equalTo($order))->willReturn(true);
        $this->_transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->with($this->equalTo($orderId))->willReturn($payuplOrderId);
        $path = 'orba_payupl/payment/repeat';
        $params = ['id' => $payuplOrderId];
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path . '/id/' . $payuplOrderId;
        $this->_urlBuilder->expects($this->once())->method('getUrl')->with(
            $this->equalTo($path),
            $this->equalTo($params)
        )->willReturn($url);
        $this->assertEquals($url, $this->_helper->getRepeatPaymentUrl($orderId));
    }

    public function testGetOrderIdIfCanRepeatFailEmptyId()
    {
        $this->assertFalse($this->_helper->getOrderIdIfCanRepeat(null));
    }

    public function testGetOrderIdIfCanRepeatFailInvalidId()
    {
        $payuplOrderId = 'invalid';
        $this->_transactionResource->expects($this->once())->method('checkIfNewestByPayuplOrderId')->with($payuplOrderId)->willReturn(false);
        $this->assertFalse($this->_helper->getOrderIdIfCanRepeat($payuplOrderId));
    }

    public function testGetOrderIdIfCanRepeatSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'valid';
        $this->_transactionResource->expects($this->once())->method('checkIfNewestByPayuplOrderId')->with($payuplOrderId)->willReturn(true);
        $this->_transactionResource->expects($this->once())->method('getOrderIdByPayuplOrderId')->with($payuplOrderId)->willReturn($orderId);
        $this->assertEquals($orderId, $this->_helper->getOrderIdIfCanRepeat($payuplOrderId));
    }
}