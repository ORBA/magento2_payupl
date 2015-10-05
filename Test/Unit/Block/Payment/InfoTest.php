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

    public function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)->getMockForAbstractClass();
        $this->_context = $this->_objectManager->getObject(
            \Magento\Framework\View\Element\Template\Context::class,
            ['layout' => $this->_layout]
        );
        $this->_block = $this->_objectManager->getObject(Info::class, [
            'context' => $this->_context
        ]);
    }

    public function testSetChild()
    {
        $this->_layout->expects($this->once())->method('createBlock')->with($this->equalTo(Info\Buttons::class));
        Util::callMethod($this->_block, '_prepareLayout');
    }
}