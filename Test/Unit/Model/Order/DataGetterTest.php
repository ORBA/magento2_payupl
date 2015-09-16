<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataGetterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataGetter
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(DataGetter::class, []);
    }

    public function testGetBasicData()
    {
        $incrementId = '0000000001';
        $currency = 'PLN';
        $amount = '10.9800';
        $description = __('Order # %1', [$incrementId]);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getIncrementId')->willReturn($incrementId);
        $order->expects($this->once())->method('getOrderCurrencyCode')->willReturn($currency);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($amount);
        $this->assertEquals([
            'currencyCode' => $currency,
            'totalAmount' => $amount * 100,
            'extOrderId' => $incrementId,
            'description' => $description,
        ], $this->_model->getBasicData($order));
    }

    public function testGetProductsData()
    {
        $name = 'Example';
        $price = '5.4900';
        $quantity = '1.5';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $orderItem = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderItemInterface::class)->getMockForAbstractClass();
        $orderItem->expects($this->once())->method('getName')->willReturn($name);
        $orderItem->expects($this->once())->method('getPriceInclTax')->willReturn($price);
        $orderItem->expects($this->once())->method('getQtyOrdered')->willReturn($quantity);
        $orderItems = [
            $orderItem
        ];
        $order->expects($this->once())->method('getAllVisibleItems')->willReturn($orderItems);
        $productsData = $this->_model->getProductsData($order);
        $this->assertEquals([
            [
                'name' => $name,
                'unitPrice' => $price * 100,
                'quantity' => $quantity
            ]
        ], $productsData);
        $this->assertInternalType('float', $productsData[0]['quantity']);
    }

    public function testGetShippingDataFree()
    {
        $shippingAmount = '0.0000';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getShippingInclTax')->willReturn($shippingAmount);
        $this->assertNull($this->_model->getShippingData($order));
    }

    public function testGetShippingDataPaid()
    {
        $shippingDescription = 'Kurier';
        $shippingAmount = '9.9900';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getShippingInclTax')->willReturn($shippingAmount);
        $order->expects($this->once())->method('getShippingDescription')->willReturn($shippingDescription);
        $this->assertEquals([
            'name' => $shippingDescription,
            'unitPrice' => $shippingAmount * 100,
            'quantity' => 1
        ], $this->_model->getShippingData($order));
    }

    public function testGetBuyerDataNoAddress()
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getBillingAddress')->willReturn(null);
        $this->assertEquals(null, $this->_model->getBuyerData($order));
    }

    public function testGetBuyerData()
    {
        $email = 'example@gmail.com';
        $phone = '500 123 456';
        $firstname = 'Jan';
        $lastname = 'Kowalski';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $orderAddress = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderAddressInterface::class)->getMockForAbstractClass();
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
        ], $this->_model->getBuyerData($order));
    }
}