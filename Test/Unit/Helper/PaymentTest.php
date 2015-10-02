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

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();
        $context = $objectManager->getObject(
            \Magento\Framework\App\Helper\Context::class,
            ['urlBuilder' => $this->_urlBuilder]
        );
        $this->_helper = $objectManager->getObject(Payment::class, [
            'context' => $context,
            'transactionResource' => $this->_transactionResource
        ]);
    }

    public function testGetRepeatPaymentUrlFail()
    {
        $orderId = 1;
        $this->_transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->with($this->equalTo($orderId))->willReturn(false);
        $this->assertFalse($this->_helper->getRepeatPaymentUrl($orderId));
    }

    public function testGetRepeatPaymentUrlSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'ABC';
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