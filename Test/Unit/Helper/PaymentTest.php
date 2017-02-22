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
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderHelper;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();
        $this->orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()
            ->getMock();
        $context = $objectManager->getObject(
            \Magento\Framework\App\Helper\Context::class,
            ['urlBuilder' => $this->urlBuilder]
        );
        $this->helper = $objectManager->getObject(Payment::class, [
            'context' => $context,
            'transactionResource' => $this->transactionResource,
            'orderHelper' => $this->orderHelper
        ]);
    }

    public function testGetStartPaymentUrlFailOrderNotFound()
    {
        $orderId = 1;
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn(false);
        $this->assertFalse($this->helper->getStartPaymentUrl($orderId));
    }

    public function testGetStartPaymentUrlFailInvalidOrder()
    {
        $orderId = 1;
        $order = $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn($order);
        $this->orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->helper->getStartPaymentUrl($orderId));
    }

    public function testGetStartPaymentUrlSuccess()
    {
        $orderId = 1;
        $order = $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn($order);
        $this->orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))
            ->willReturn(true);
        $path = 'orba_payupl/payment/start';
        $params = ['id' => $orderId];
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path . '/id/' . $orderId;
        $this->urlBuilder->expects($this->once())->method('getUrl')->with(
            $this->equalTo($path),
            $this->equalTo($params)
        )->willReturn($url);
        $this->assertEquals($url, $this->helper->getStartPaymentUrl($orderId));
    }

    public function testGetRepeatPaymentUrlFailOrderNotFound()
    {
        $orderId = 1;
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn(false);
        $this->assertFalse($this->helper->getRepeatPaymentUrl($orderId));
    }

    public function testGetRepeatPaymentUrlFailInvalidOrder()
    {
        $orderId = 1;
        $order = $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn($order);
        $this->orderHelper->expects($this->once())->method('canRepeatPayment')->with($this->equalTo($order))
            ->willReturn(false);
        $this->assertFalse($this->helper->getRepeatPaymentUrl($orderId));
    }

    public function testGetRepeatPaymentUrlSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'ABC';
        $order = $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn($order);
        $this->orderHelper->expects($this->once())->method('canRepeatPayment')->with($this->equalTo($order))
            ->willReturn(true);
        $this->transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')
            ->with($this->equalTo($orderId))->willReturn($payuplOrderId);
        $path = 'orba_payupl/payment/repeat';
        $params = ['id' => base64_encode($payuplOrderId)];
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path . '/id/' . base64_encode($payuplOrderId);
        $this->urlBuilder->expects($this->once())->method('getUrl')->with(
            $this->equalTo($path),
            $this->equalTo($params)
        )->willReturn($url);
        $this->assertEquals($url, $this->helper->getRepeatPaymentUrl($orderId));
    }

    public function testGetOrderIdIfCanRepeatFailEmptyId()
    {
        $this->assertFalse($this->helper->getOrderIdIfCanRepeat(null));
    }

    public function testGetOrderIdIfCanRepeatFailInvalidId()
    {
        $payuplOrderId = 'invalid';
        $this->transactionResource->expects($this->once())->method('checkIfNewestByPayuplOrderId')->with($payuplOrderId)
            ->willReturn(false);
        $this->assertFalse($this->helper->getOrderIdIfCanRepeat($payuplOrderId));
    }

    public function testGetOrderIdIfCanRepeatSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'valid';
        $this->transactionResource->expects($this->once())->method('checkIfNewestByPayuplOrderId')->with($payuplOrderId)
            ->willReturn(true);
        $this->transactionResource->expects($this->once())->method('getOrderIdByPayuplOrderId')->with($payuplOrderId)
            ->willReturn($orderId);
        $this->assertEquals($orderId, $this->helper->getOrderIdIfCanRepeat($payuplOrderId));
    }
}
