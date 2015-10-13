<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Sales\Order;

use Orba\Payupl\Model\Sales\Order;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderStatusCollectionFactory;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $this->_orderStatusCollectionFactory = $this->getMockBuilder(\Magento\Sales\Model\Resource\Order\Status\CollectionFactory::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Config::class, [
            'orderStatusCollectionFactory' => $this->_orderStatusCollectionFactory,
            'scopeConfig' => $this->_scopeConfig
        ]);
    }

    public function testGetDefaultPendingPaymentStatus()
    {
        $state = Order::STATE_PENDING_PAYMENT;
        $status = 'pending_payment';
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->with($this->equalTo(Config::XML_PATH_ORDER_STATUS_NEW), $this->equalTo('store'))->willReturn($status);
        $this->assertEquals($status, $this->_model->getStateDefaultStatus($state));
    }

    public function testGetDefaultHoldedStatus()
    {
        $state = Order::STATE_HOLDED;
        $status = 'holded';
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->with($this->equalTo(Config::XML_PATH_ORDER_STATUS_HOLDED), $this->equalTo('store'))->willReturn($status);
        $this->assertEquals($status, $this->_model->getStateDefaultStatus($state));
    }

    public function testGetDefaultProcessingStatus()
    {
        $state = Order::STATE_PROCESSING;
        $status = 'processing';
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->with($this->equalTo(Config::XML_PATH_ORDER_STATUS_PROCESSING), $this->equalTo('store'))->willReturn($status);
        $this->assertEquals($status, $this->_model->getStateDefaultStatus($state));
    }
}