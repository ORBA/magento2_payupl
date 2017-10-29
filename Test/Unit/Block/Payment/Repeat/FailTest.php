<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Payment\Repeat;

class FailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

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
        $this->session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->disableOriginalConstructor()
            ->setMethods(['getLastOrderId'])->getMock();
        $this->paymentHelper = $this->getMockBuilder(\Orba\Payupl\Helper\Payment::class)->disableOriginalConstructor()
            ->getMock();
        $this->block = $objectManager->getObject(Fail::class, [
            'session' => $this->session,
            'paymentHelper' => $this->paymentHelper
        ]);
    }

    public function testGetPaymentUrlFail()
    {
        $this->session->expects($this->once())->method('getLastOrderId')->willReturn(null);
        $this->assertFalse($this->block->getPaymentUrl());
    }

    public function testGetPaymentUrlSuccessRepeat()
    {
        $orderId = 1;
        $url = 'http://repeat.url';
        $this->session->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $this->paymentHelper->expects($this->once())->method('getRepeatPaymentUrl')->with($this->equalTo($orderId))
            ->willReturn($url);
        $this->assertEquals($url, $this->block->getPaymentUrl());
    }

    public function testGetPaymentUrlSuccessNew()
    {
        $orderId = 1;
        $url = 'http://start-new.url';
        $this->session->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $this->paymentHelper->expects($this->once())->method('getRepeatPaymentUrl')->with($this->equalTo($orderId))
            ->willReturn(false);
        $this->paymentHelper->expects($this->once())->method('getStartPaymentUrl')->with($this->equalTo($orderId))
            ->willReturn($url);
        $this->assertEquals($url, $this->block->getPaymentUrl());
    }
}
