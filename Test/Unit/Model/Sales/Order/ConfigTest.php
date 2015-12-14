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
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderStatusCollectionFactory;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->orderStatusCollectionFactory = $this
            ->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(Config::class, [
            'orderStatusCollectionFactory' => $this->orderStatusCollectionFactory,
            'scopeConfig' => $this->scopeConfig
        ]);
    }

    public function testGetDefaultPendingPaymentStatus()
    {
        $state = Order::STATE_PENDING_PAYMENT;
        $status = 'pending_payment';
        $this->scopeConfig->expects($this->at(0))->method('getValue')
            ->with($this->equalTo(Config::XML_PATH_ORDER_STATUS_NEW), $this->equalTo('store'))->willReturn($status);
        $this->assertEquals($status, $this->model->getStateDefaultStatus($state));
    }

    public function testGetDefaultHoldedStatus()
    {
        $state = Order::STATE_HOLDED;
        $status = 'holded';
        $this->scopeConfig->expects($this->at(0))->method('getValue')
            ->with($this->equalTo(Config::XML_PATH_ORDER_STATUS_HOLDED), $this->equalTo('store'))->willReturn($status);
        $this->assertEquals($status, $this->model->getStateDefaultStatus($state));
    }

    public function testGetDefaultProcessingStatus()
    {
        $state = Order::STATE_PROCESSING;
        $status = 'processing';
        $this->scopeConfig->expects($this->at(0))->method('getValue')
            ->with($this->equalTo(Config::XML_PATH_ORDER_STATUS_PROCESSING), $this->equalTo('store'))
            ->willReturn($status);
        $this->assertEquals($status, $this->model->getStateDefaultStatus($state));
    }
}
