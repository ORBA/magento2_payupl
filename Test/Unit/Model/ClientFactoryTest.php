<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CientFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)->getMockForAbstractClass();
        $this->_model = $objectManager->getObject(ClientFactory::class, [
            'objectManager' => $this->_objectManager
        ]);
    }

    public function testCreate()
    {
        $object = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->_objectManager->expects($this->once())->method('create')->with(
            $this->equalTo(Client\Rest::class),
            $this->equalTo([])
        )->willReturn($object);
        $this->assertEquals($object, $this->_model->create());
    }
}