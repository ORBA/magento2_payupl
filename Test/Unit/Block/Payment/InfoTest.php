<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Payment;

use Orba\Payupl\Test\Util;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_context;

    /**
     * @var Info
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_clientFactory;

    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)->getMockForAbstractClass();
        $this->_context = $this->_objectManager->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            ['layout' => $this->_layout]
        );
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)->disableOriginalConstructor()->getMock();
        $this->_block = $this->_objectManager->getObject(Info::class, [
            'context' => $this->_context,
            'transactionResource' => $this->_transactionResource,
            'clientFactory' => $this->_clientFactory
        ]);
    }

    public function testSetChild()
    {
        $this->_layout->expects($this->once())->method('createBlock')->with($this->equalTo(Info\Buttons::class));
        Util::callMethod($this->_block, '_prepareLayout');
    }
    
    public function testSpecificInformation()
    {
        $transport = new \Magento\Framework\DataObject();
        Util::setProperty($this->_block, '_paymentSpecificInformation', $transport);
        $status = 'status';
        $statusDescription = 'desc';
        $orderId = 1;
        $info = $this->getMockBuilder(\Magento\Payment\Model\Info::class)->setMethods(['getParentId'])->disableOriginalConstructor()->getMock();
        $info->expects($this->once())->method('getParentId')->willReturn($orderId);
        $this->_transactionResource->expects($this->once())->method('getLastStatusByOrderId')->with($this->equalTo($orderId))->willReturn($status);
        $this->_block->setData('info', $info);
        $client = $this->_getClientMock();
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->disableOriginalConstructor()->getMock();
        $clientOrderHelper->expects($this->once())->method('getStatusDescription')->with($this->equalTo($status))->willReturn($statusDescription);
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $transport->setData((string) __('Status'), $statusDescription);
        $this->assertSame($transport, Util::callMethod($this->_block, '_prepareSpecificInformation'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->_clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }
}