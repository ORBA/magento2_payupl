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

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $this->_urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMock();
        $this->_model = $objectManagerHelper->getObject(
            Payupl::class,
            [
                'scopeConfig' => $this->_scopeConfig,
                'urlBuilder' => $this->_urlBuilder
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
    
    public function testIsAvailableActiveAllowedCarrier()
    {
        $this->_expectConfigActive(true);
        $shippingMethod = 'flatrate_flatrate';
        $this->_expectShippingMethodConfig($shippingMethod);
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
    
    public function testCheckoutRedirectUrl()
    {
        $path = 'orba_payupl/payment/start';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $this->_urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->assertEquals($url, $this->_model->getCheckoutRedirectUrl());
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
}