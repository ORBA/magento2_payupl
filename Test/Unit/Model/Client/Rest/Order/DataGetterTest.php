<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataGetterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataGetter
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extOrderIdHelper;

    /**
     * @var \Magento\Framework\View\Context
     */
    protected $context;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $this->configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Rest\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->extOrderIdHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order\ExtOrderId::class)
            ->disableOriginalConstructor()->getMock();
        $this->context = $objectManagerHelper->getObject(
            \Magento\Framework\View\Context::class,
            ['urlBuilder' => $this->urlBuilder]
        );
        $this->model = $objectManagerHelper->getObject(
            DataGetter::class,
            [
                'context' => $this->context,
                'configHelper' => $this->configHelper,
                'extOrderIdHelper' => $this->extOrderIdHelper
            ]
        );
    }
    
    public function testContinueUrl()
    {
        $path = 'orba_payupl/payment/end';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $this->urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->assertEquals($url, $this->model->getContinueUrl());
    }

    public function testNotifyUrl()
    {
        $path = 'orba_payupl/payment/notify';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $this->urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->assertEquals($url, $this->model->getNotifyUrl());
    }

    public function testCustomerIp()
    {
        $ip = '127.0.0.1';
        $_SERVER['REMOTE_ADDR'] = $ip;
        $this->assertEquals($ip, $this->model->getCustomerIp());
    }

    public function testMerchantPosId()
    {
        $merchantPosId = '123456';
        $this->configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('merchant_pos_id'))
            ->willReturn($merchantPosId);
        $this->assertEquals($merchantPosId, $this->model->getMerchantPosId());
    }

    public function testGetBasicData()
    {
        $incrementId = '0000000001';
        $currency = 'PLN';
        $amount = '10.9800';
        $description = __('Order # %1', [$incrementId]);
        $extOrderId = '0000000001-1';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getIncrementId')->willReturn($incrementId);
        $order->expects($this->once())->method('getOrderCurrencyCode')->willReturn($currency);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($amount);
        $this->extOrderIdHelper->expects($this->once())->method('generate')->with($this->equalTo($order))
            ->willReturn($extOrderId);
        $this->assertEquals([
            'currencyCode' => $currency,
            'totalAmount' => $amount * 100,
            'extOrderId' => $extOrderId,
            'description' => $description,
        ], $this->model->getBasicData($order));
    }

    public function testGetProductsData()
    {
        $name = 'Example';
        $price = '5.4900';
        $quantity = '1.5';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $orderItem = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderItemInterface::class)
            ->getMockForAbstractClass();
        $orderItem->expects($this->once())->method('getName')->willReturn($name);
        $orderItem->expects($this->once())->method('getPriceInclTax')->willReturn($price);
        $orderItem->expects($this->once())->method('getQtyOrdered')->willReturn($quantity);
        $orderItems = [
            $orderItem
        ];
        $order->expects($this->once())->method('getAllVisibleItems')->willReturn($orderItems);
        $productsData = $this->model->getProductsData($order);
        $this->assertEquals([
            [
                'name' => $name,
                'unitPrice' => $price * 100,
                'quantity' => $quantity
            ]
        ], $productsData);
        $this->assertInternalType('float', $productsData[0]['quantity']);
    }

    public function testGetShippingDataMethodUnset()
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getShippingMethod')->willReturn(null);
        $this->assertNull($this->model->getShippingData($order));
    }

    public function testGetShippingDataMethodSetFree()
    {
        $shippingMethod = 'flatrate_flatrate';
        $shippingAmount = '0.0000';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $order->expects($this->once())->method('getShippingInclTax')->willReturn($shippingAmount);
        $this->assertNull($this->model->getShippingData($order));
    }

    public function testGetShippingDataMethodSet()
    {
        $shippingMethod = 'flatrate_flatrate';
        $shippingDescription = 'Kurier';
        $shippingAmount = '9.9900';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getShippingMethod')->willReturn($shippingMethod);
        $order->expects($this->once())->method('getShippingInclTax')->willReturn($shippingAmount);
        $order->expects($this->once())->method('getShippingDescription')->willReturn($shippingDescription);
        $this->assertEquals([
            'name' => __('Shipping Method') . ': ' . $shippingDescription,
            'unitPrice' => $shippingAmount * 100,
            'quantity' => 1
        ], $this->model->getShippingData($order));
    }

    public function testGetBuyerDataNoAddress()
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getBillingAddress')->willReturn(null);
        $this->assertEquals(null, $this->model->getBuyerData($order));
    }

    public function testGetBuyerData()
    {
        $email = 'example@gmail.com';
        $phone = '500 123 456';
        $firstname = 'Jan';
        $lastname = 'Kowalski';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $orderAddress = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderAddressInterface::class)
            ->getMockForAbstractClass();
        $orderAddress->expects($this->once())->method('getEmail')->willReturn($email);
        $orderAddress->expects($this->once())->method('getTelephone')->willReturn($phone);
        $orderAddress->expects($this->once())->method('getFirstname')->willReturn($firstname);
        $orderAddress->expects($this->once())->method('getLastname')->willReturn($lastname);
        $order->expects($this->once())->method('getBillingAddress')->willReturn($orderAddress);
        $this->assertEquals([
            'email' => $email,
            'phone' => $phone,
            'firstName' => $firstname,
            'lastName' => $lastname
        ], $this->model->getBuyerData($order));
    }
}
