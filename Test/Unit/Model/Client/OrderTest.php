<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client\Order
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataAdder;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dataValidator = $this->getMockBuilder(\Orba\Payupl\Model\Client\Order\DataValidator::class)->getMock();
        $this->_dataAdder = $this->getMockBuilder(\Orba\Payupl\Model\Client\Order\DataAdder::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client\Order::class,
            [
                'dataValidator' => $this->_dataValidator,
                'dataAdder' => $this->_dataAdder
            ]
        );
    }

    public function testValidationFailedEmpty()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validate());
    }

    public function testValidationFailedInvalidBasicData()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateBasicData')->willReturn(false);
        $this->assertFalse($this->_model->validate());
    }

    public function testValidationFailedInvalidProductsData()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateBasicData')->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateProductsData')->willReturn(false);
        $this->assertFalse($this->_model->validate());
    }

    public function testDataAdder()
    {
        $data = [
            'example' => true
        ];
        $this->_dataAdder->expects($this->once())->method('getContinueUrl');
        $this->_dataAdder->expects($this->once())->method('getNotifyUrl');
        $this->_dataAdder->expects($this->once())->method('getCustomerIp');
        $this->_dataAdder->expects($this->once())->method('getMerchantPosId');
        $extendedData = $this->_model->addSpecialData($data);
        $this->assertEquals($data, array_intersect($extendedData, $data));
        $this->assertArrayHasKey('continueUrl', $extendedData);
        $this->assertArrayHasKey('notifyUrl', $extendedData);
        $this->assertArrayHasKey('customerIp', $extendedData);
        $this->assertArrayHasKey('merchantPosId', $extendedData);
    }

}