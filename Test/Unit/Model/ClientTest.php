<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configSetter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderDataHelper;

    /**
     * @var ObjectManager
     */
    protected $_objectManagerHelper;

    public function setUp()
    {
        $this->_objectManagerHelper = new ObjectManager($this);
        $this->_configSetter = $this->getMockBuilder(\Orba\Payupl\Model\Client\Config::class)->disableOriginalConstructor()->getMock();
        $this->_orderDataHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Order::class)->disableOriginalConstructor()->getMock();
        $this->_model = $this->_getModel();
    }

    public function testSetConfigInConstructor()
    {
        $this->_configSetter->expects($this->once())->method('setConfig')->willReturn(true);
        // This will run constructor.
        $this->_getModel();
    }

    public function testOrderInvalidData()
    {
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'Order request data array is invalid.');
        $this->_orderDataHelper->expects($this->once())->method('validate')->willReturn(false);
        $this->_model->order();
    }

    public function testOrderValidData()
    {
        $this->_orderDataHelper->expects($this->once())->method('validate')->willReturn(true);
        $this->_orderDataHelper->expects($this->once())->method('addSpecialData');
        $this->_model->order();
    }
    
    /**
     * @return object
     */
    protected function _getModel()
    {
        return $this->_objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client::class,
            [
                'configSetter' => $this->_configSetter,
                'orderDataHelper' => $this->_orderDataHelper
            ]
        );
    }
}