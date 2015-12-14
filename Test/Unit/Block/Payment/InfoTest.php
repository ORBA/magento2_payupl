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
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layout;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var Info
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();
        $this->context = $this->objectManager->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            ['layout' => $this->layout]
        );
        $this->transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->block = $this->objectManager->getObject(Info::class, [
            'context' => $this->context,
            'transactionResource' => $this->transactionResource,
            'clientFactory' => $this->clientFactory
        ]);
    }

    public function testSetChild()
    {
        $this->layout->expects($this->once())->method('createBlock')->with($this->equalTo(Info\Buttons::class));
        Util::callMethod($this->block, '_prepareLayout');
    }
    
    public function testSpecificInformation()
    {
        $transport = new \Magento\Framework\DataObject();
        Util::setProperty($this->block, '_paymentSpecificInformation', $transport);
        $status = 'status';
        $statusDescription = 'desc';
        $orderId = 1;
        $info = $this->getMockBuilder(\Magento\Payment\Model\Info::class)->setMethods(['getParentId'])
            ->disableOriginalConstructor()->getMock();
        $info->expects($this->once())->method('getParentId')->willReturn($orderId);
        $this->transactionResource->expects($this->once())->method('getLastStatusByOrderId')
            ->with($this->equalTo($orderId))->willReturn($status);
        $this->block->setData('info', $info);
        $client = $this->getClientMock();
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)
            ->disableOriginalConstructor()->getMock();
        $clientOrderHelper->expects($this->once())->method('getStatusDescription')->with($this->equalTo($status))
            ->willReturn($statusDescription);
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $transport->setData((string) __('Status'), $statusDescription);
        $this->assertSame($transport, Util::callMethod($this->block, '_prepareSpecificInformation'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }
}
