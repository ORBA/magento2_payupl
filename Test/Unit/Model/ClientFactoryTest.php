<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientFactory
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->model = $objectManager->getObject(ClientFactory::class, [
            'objectManager' => $this->objectManager,
            'scopeConfig' => $this->scopeConfig
        ]);
    }

    public function testCreateClassic()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with($this->equalTo(Payupl::XML_PATH_CLASSIC_API), $this->equalTo('store'))->willReturn(true);
        $object = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->objectManager->expects($this->once())->method('create')->with(
            $this->equalTo(Client\Classic::class),
            $this->equalTo([])
        )->willReturn($object);
        $this->assertEquals($object, $this->model->create());
    }

    public function testCreateRest()
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')
            ->with($this->equalTo(Payupl::XML_PATH_CLASSIC_API), $this->equalTo('store'))->willReturn(false);
        $object = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->objectManager->expects($this->once())->method('create')->with(
            $this->equalTo(Client\Rest::class),
            $this->equalTo([])
        )->willReturn($object);
        $this->assertEquals($object, $this->model->create());
    }
}
