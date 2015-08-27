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
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->willReturn(0);
        $this->assertFalse($this->_model->isAvailable($this->_getQuoteMock()));
    }
    
    public function testIsAvailableActive()
    {
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->willReturn(1);
        $this->assertTrue($this->_model->isAvailable($this->_getQuoteMock()));
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
}