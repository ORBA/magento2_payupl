<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class PayuplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Payupl
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $this->_model = $objectManagerHelper->getObject(
            \Orba\Payupl\Model\Payupl::class,
            [
                'scopeConfig' => $this->_scopeConfig
            ]
        );

    }

    public function testIsAvailableNoQuote()
    {
        $this->assertFalse($this->_model->isAvailable());
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