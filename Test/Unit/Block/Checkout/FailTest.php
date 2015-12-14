<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Checkout;

class FailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var Fail
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelper;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()->setMethods(['getLastOrderId'])->getMock();
        $this->paymentHelper = $this->getMockBuilder(\Orba\Payupl\Helper\Payment::class)->disableOriginalConstructor()
            ->getMock();
        $this->block = $objectManager->getObject(Fail::class, [
            'checkoutSession' => $this->checkoutSession,
            'paymentHelper' => $this->paymentHelper
        ]);
    }

    public function testGetPaymentUrlFail()
    {
        $this->checkoutSession->expects($this->once())->method('getLastOrderId')->willReturn(null);
        $this->assertFalse($this->block->getPaymentUrl());
    }

    public function testGetPaymentUrlSuccessRepeat()
    {
        $orderId = 1;
        $url = 'http://repeat.url';
        $this->checkoutSession->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $this->paymentHelper->expects($this->once())->method('getRepeatPaymentUrl')->with($this->equalTo($orderId))
            ->willReturn($url);
        $this->assertEquals($url, $this->block->getPaymentUrl());
    }

    public function testGetPaymentUrlSuccessNew()
    {
        $orderId = 1;
        $url = 'http://start-new.url';
        $this->checkoutSession->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $this->paymentHelper->expects($this->once())->method('getRepeatPaymentUrl')->with($this->equalTo($orderId))
            ->willReturn(false);
        $this->paymentHelper->expects($this->once())->method('getStartPaymentUrl')->with($this->equalTo($orderId))
            ->willReturn($url);
        $this->assertEquals($url, $this->block->getPaymentUrl());
    }
}
