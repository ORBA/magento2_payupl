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
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)->getMockForAbstractClass();
        $this->_scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $this->_model = $objectManager->getObject(ClientFactory::class, [
            'objectManager' => $this->_objectManager,
            'scopeConfig' => $this->_scopeConfig
        ]);
    }

    public function testCreateClassic()
    {
        $this->_scopeConfig->expects($this->once())->method('isSetFlag')->with($this->equalTo(Payupl::XML_PATH_CLASSIC_API), $this->equalTo('store'))->willReturn(true);
        $object = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->_objectManager->expects($this->once())->method('create')->with(
            $this->equalTo(Client\Classic::class),
            $this->equalTo([])
        )->willReturn($object);
        $this->assertEquals($object, $this->_model->create());
    }

    public function testCreateRest()
    {
        $this->_scopeConfig->expects($this->once())->method('isSetFlag')->with($this->equalTo(Payupl::XML_PATH_CLASSIC_API), $this->equalTo('store'))->willReturn(false);
        $object = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->_objectManager->expects($this->once())->method('create')->with(
            $this->equalTo(Client\Rest::class),
            $this->equalTo([])
        )->willReturn($object);
        $this->assertEquals($object, $this->_model->create());
    }
}