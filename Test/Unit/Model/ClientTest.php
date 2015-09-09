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
    protected $_configHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderHelper;

    /**
     * @var ObjectManager
     */
    protected $_objectManagerHelper;

    public function setUp()
    {
        $this->_objectManagerHelper = new ObjectManager($this);
        $this->_configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Config::class)->disableOriginalConstructor()->getMock();
        $this->_orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Order::class)->disableOriginalConstructor()->getMock();
        $this->_model = $this->_getModel();
    }

    public function testSetConfigInConstructor()
    {
        $this->_configHelper->expects($this->once())->method('setConfig')->willReturn(true);
        // This will run constructor.
        $this->_getModel();
    }

    public function testOrderCreateInvalidData()
    {
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'Order request data array is invalid.');
        $this->_orderHelper->expects($this->once())->method('validate')->willReturn(false);
        $this->_model->orderCreate();
    }

    public function testOrderCreateFail()
    {
        $data = ['data'];
        $dataExtended = ['data_extended'];
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'There was a problem while processing order request.');
        $this->_orderHelper->expects($this->once())->method('validate')->willReturn(true);
        $this->_orderHelper->expects($this->once())->method('addSpecialData')->with($this->equalTo($data))->willReturn($dataExtended);
        $this->_orderHelper->expects($this->once())->method('create')->with($this->equalTo($dataExtended))->willReturn(false);
        $this->_model->orderCreate($data);
    }

    public function testOrderCreateSuccess()
    {
        $data = ['data'];
        $dataExtended = ['data_extended'];
        $result = ['result'];
        $this->_orderHelper->expects($this->once())->method('validate')->with($this->equalTo($data))->willReturn(true);
        $this->_orderHelper->expects($this->once())->method('addSpecialData')->with($this->equalTo($data))->willReturn($dataExtended);
        $this->_orderHelper->expects($this->once())->method('create')->with($this->equalTo($dataExtended))->willReturn($result);
        $this->assertEquals($result, $this->_model->orderCreate($data));
    }
    
    /**
     * @return object
     */
    protected function _getModel()
    {
        return $this->_objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client::class,
            [
                'configHelper' => $this->_configHelper,
                'orderHelper' => $this->_orderHelper
            ]
        );
    }
}