<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PayuplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payupl
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paytypeHelper;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMock();
        $this->clientFactory = $this->getMockBuilder(ClientFactory::class)->disableOriginalConstructor()->getMock();
        $this->transactionResource = $this->getMockBuilder(ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->paytypeHelper = $this->getMockBuilder(Order\Paytype::class)->disableOriginalConstructor()->getMock();
        $this->model = $objectManagerHelper->getObject(
            Payupl::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'urlBuilder' => $this->urlBuilder,
                'clientFactory' => $this->clientFactory,
                'transactionResource' => $this->transactionResource,
                'paytypeHelper' => $this->paytypeHelper
            ]
        );

    }

    public function testIsAvailableNoQuote()
    {
        $this->expectConfigActive(true);
        $this->assertTrue($this->model->isAvailable());
    }

    public function testIsAvailableNotActive()
    {
        $this->expectConfigActive(false);
        $this->assertFalse($this->model->isAvailable($this->getQuoteMock()));
    }

    public function testIsAvailableActiveNoCarrier()
    {
        $this->expectConfigActive(true);
        $shippingMethod = null;
        $shippingAddress = $this->getShippingAddressMockWithShippingMethod($shippingMethod);
        $quote = $this->getQuoteMockWithShippingAddress($shippingAddress);
        $this->assertTrue($this->model->isAvailable($quote));
    }

    public function testIsAvailableActiveNotAllowedCarrier()
    {
        $this->expectConfigActive(true);
        $shippingMethodConfig = 'flatrate_flatrate';
        $shippingMethodAddress = 'tablerate_tablerate';
        $this->expectShippingMethodConfig($shippingMethodConfig);
        $shippingAddress = $this->getShippingAddressMockWithShippingMethod($shippingMethodAddress);
        $quote = $this->getQuoteMockWithShippingAddress($shippingAddress);
        $this->assertFalse($this->model->isAvailable($quote));
    }

    public function testIsAvailableActiveAllowedCarrierNoPaytypes()
    {
        $this->expectConfigActive(true);
        $shippingMethod = 'flatrate_flatrate';
        $this->expectShippingMethodConfig($shippingMethod);
        $shippingAddress = $this->getShippingAddressMockWithShippingMethod($shippingMethod);
        $quote = $this->getQuoteMockWithShippingAddress($shippingAddress);
        $this->paytypeHelper->expects($this->once())->method('getAllForQuote')->with($this->equalTo($quote))
            ->willReturn(false);
        $this->assertTrue($this->model->isAvailable($quote));
    }

    public function testIsAvailableActiveAllowedCarrierEmptyPaytypes()
    {
        $this->expectConfigActive(true);
        $shippingMethod = 'flatrate_flatrate';
        $this->expectShippingMethodConfig($shippingMethod);
        $shippingAddress = $this->getShippingAddressMockWithShippingMethod($shippingMethod);
        $quote = $this->getQuoteMockWithShippingAddress($shippingAddress);
        $this->paytypeHelper->expects($this->once())->method('getAllForQuote')->with($this->equalTo($quote))
            ->willReturn([]);
        $this->assertFalse($this->model->isAvailable($quote));
    }

    public function testIsAvailableActiveAllowedCarrierSomePaytypes()
    {
        $this->expectConfigActive(true);
        $shippingMethod = 'flatrate_flatrate';
        $this->expectShippingMethodConfig($shippingMethod);
        $shippingAddress = $this->getShippingAddressMockWithShippingMethod($shippingMethod);
        $quote = $this->getQuoteMockWithShippingAddress($shippingAddress);
        $this->paytypeHelper->expects($this->once())->method('getAllForQuote')->with($this->equalTo($quote))
            ->willReturn(['paytypes']);
        $this->assertTrue($this->model->isAvailable($quote));
    }

    public function testCheckoutRedirectUrl()
    {
        $path = 'orba_payupl/payment/start';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $this->urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->assertEquals($url, $this->model->getCheckoutRedirectUrl());
    }

    public function testRefund()
    {
        $payuplOrderId = 'ABC';
        $amount = 2.22;
        $orderId = 1;
        $incrementOrderId = '00000001';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $order->expects($this->once())->method('getIncrementId')->willReturn($incrementOrderId);
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->once())->method('getOrder')->willReturn($order);
        $this->transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')
            ->willReturn($payuplOrderId);
        $client = $this->getClientMock();
        $client->expects($this->once())->method('refundCreate')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo(__('Refund for order # %1', $incrementOrderId)),
            $this->equalTo($amount * 100)
        )->willReturn(true);
        $this->assertInstanceOf(Payupl::class, $this->model->refund($payment, $amount));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteMock()
    {
        return $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(['getStoreId', 'getShippingAddress'])
            ->getMockForAbstractClass();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getShippingAddressMock()
    {
        return $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->setMethods(['getShippingMethod'])
            ->getMockForAbstractClass();
    }

    /**
     * @param bool $isActive
     */
    protected function expectConfigActive($isActive)
    {
        $this->scopeConfig->expects($this->at(0))->method('getValue')->willReturn((int)$isActive);
    }

    /**
     * @param string $shippingMethod
     */
    protected function expectShippingMethodConfig($shippingMethod)
    {
        $this->scopeConfig->expects($this->at(1))->method('getValue')->willReturn($shippingMethod);
    }

    /**
     * @param $shippingMethod
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getShippingAddressMockWithShippingMethod($shippingMethod)
    {
        $shippingAddress = $this->getShippingAddressMock();
        $shippingAddress->expects($this->any())->method('getShippingMethod')->willReturn($shippingMethod);
        return $shippingAddress;
    }

    /**
     * @param $shippingAddress
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteMockWithShippingAddress($shippingAddress)
    {
        $quote = $this->getQuoteMock();
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddress);
        return $quote;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }
}
