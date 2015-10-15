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
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paytypeHelper;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $this->_urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMock();
        $this->_clientFactory = $this->getMockBuilder(ClientFactory::class)->disableOriginalConstructor()->getMock();
        $this->_transactionResource = $this->getMockBuilder(Resource\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_paytypeHelper = $this->getMockBuilder(Order\Paytype::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            Payupl::class,
            [
                'scopeConfig' => $this->_scopeConfig,
                'urlBuilder' => $this->_urlBuilder,
                'clientFactory' => $this->_clientFactory,
                'transactionResource' => $this->_transactionResource,
                'paytypeHelper' => $this->_paytypeHelper
            ]
        );

    }

    public function testIsAvailableNoQuote()
    {
        $this->_expectConfigActive(true);
        $this->assertTrue($this->_model->isAvailable());
    }

    public function testIsAvailableNotActive()
    {
        $this->_expectConfigActive(false);
        $this->assertFalse($this->_model->isAvailable($this->_getQuoteMock()));
    }

    public function testIsAvailableActiveNoCarrier()
    {
        $this->_expectConfigActive(true);
        $shippingMethod = null;
        $shippingAddress = $this->_getShippingAddressMockWithShippingMethod($shippingMethod);
        $quote = $this->_getQuoteMockWithShippingAddress($shippingAddress);
        $this->assertTrue($this->_model->isAvailable($quote));
    }
    
    public function testIsAvailableActiveNotAllowedCarrier()
    {
        $this->_expectConfigActive(true);
        $shippingMethodConfig = 'flatrate_flatrate';
        $shippingMethodAddress = 'tablerate_tablerate';
        $this->_expectShippingMethodConfig($shippingMethodConfig);
        $shippingAddress = $this->_getShippingAddressMockWithShippingMethod($shippingMethodAddress);
        $quote = $this->_getQuoteMockWithShippingAddress($shippingAddress);
        $this->assertFalse($this->_model->isAvailable($quote));
    }

    public function testIsAvailableActiveAllowedCarrierNoPaytypes()
    {
        $this->_expectConfigActive(true);
        $shippingMethod = 'flatrate_flatrate';
        $this->_expectShippingMethodConfig($shippingMethod);
        $shippingAddress = $this->_getShippingAddressMockWithShippingMethod($shippingMethod);
        $quote = $this->_getQuoteMockWithShippingAddress($shippingAddress);
        $this->_paytypeHelper->expects($this->once())->method('getAllForQuote')->with($this->equalTo($quote))->willReturn(false);
        $this->assertTrue($this->_model->isAvailable($quote));
    }

    public function testIsAvailableActiveAllowedCarrierEmptyPaytypes()
    {
        $this->_expectConfigActive(true);
        $shippingMethod = 'flatrate_flatrate';
        $this->_expectShippingMethodConfig($shippingMethod);
        $shippingAddress = $this->_getShippingAddressMockWithShippingMethod($shippingMethod);
        $quote = $this->_getQuoteMockWithShippingAddress($shippingAddress);
        $this->_paytypeHelper->expects($this->once())->method('getAllForQuote')->with($this->equalTo($quote))->willReturn([]);
        $this->assertFalse($this->_model->isAvailable($quote));
    }

    public function testIsAvailableActiveAllowedCarrierSomePaytypes()
    {
        $this->_expectConfigActive(true);
        $shippingMethod = 'flatrate_flatrate';
        $this->_expectShippingMethodConfig($shippingMethod);
        $shippingAddress = $this->_getShippingAddressMockWithShippingMethod($shippingMethod);
        $quote = $this->_getQuoteMockWithShippingAddress($shippingAddress);
        $this->_paytypeHelper->expects($this->once())->method('getAllForQuote')->with($this->equalTo($quote))->willReturn(['paytypes']);
        $this->assertTrue($this->_model->isAvailable($quote));
    }

    public function testCheckoutRedirectUrl()
    {
        $path = 'orba_payupl/payment/start';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $this->_urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->assertEquals($url, $this->_model->getCheckoutRedirectUrl());
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
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $payment->expects($this->once())->method('getOrder')->willReturn($order);
        $this->_transactionResource->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->willReturn($payuplOrderId);
        $client = $this->_getClientMock();
        $client->expects($this->once())->method('refundCreate')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo(__('Refund for order # %1', $incrementOrderId)),
            $this->equalTo($amount * 100)
        )->willReturn(true);
        $this->assertInstanceOf(Payupl::class, $this->_model->refund($payment, $amount));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getQuoteMock()
    {
        return $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(['getStoreId'])
            ->getMockForAbstractClass();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getShippingAddressMock()
    {
        return $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->setMethods(['getShippingMethod'])
            ->getMockForAbstractClass();
    }

    /**
     * @param bool $isActive
     */
    protected function _expectConfigActive($isActive)
    {
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->willReturn((int) $isActive);
    }

    /**
     * @param string $shippingMethod
     */
    protected function _expectShippingMethodConfig($shippingMethod)
    {
        $this->_scopeConfig->expects($this->at(1))->method('getValue')->willReturn($shippingMethod);
    }

    /**
     * @param $shippingMethod
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getShippingAddressMockWithShippingMethod($shippingMethod)
    {
        $shippingAddress = $this->_getShippingAddressMock();
        $shippingAddress->expects($this->any())->method('getShippingMethod')->willReturn($shippingMethod);
        return $shippingAddress;
    }

    /**
     * @param $shippingAddress
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getQuoteMockWithShippingAddress($shippingAddress)
    {
        $quote = $this->_getQuoteMock();
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddress);
        return $quote;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->_clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }
}