<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment\Repeat;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Error
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactory;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->controller = $objectManager->getObject(Error::class, [
            'context' => $context,
            'resultPageFactory' => $this->resultPageFactory
        ]);
    }

    public function testExecute()
    {
        $title = $this->getMockBuilder('Magento\Framework\View\Page\Title')->disableOriginalConstructor()->getMock();
        $title->expects($this->once())->method('set')->with(__('Payment Error'));
        $config = $this->getMockBuilder('Magento\Framework\View\Page\Config')->disableOriginalConstructor()->getMock();
        $config->expects($this->once())->method('getTitle')->willReturn($title);
        $page = $this->getMockBuilder('Magento\Framework\View\Result\Page')->disableOriginalConstructor()->getMock();
        $page->expects($this->once())->method('getConfig')->willReturn($config);
        $this->resultPageFactory->expects($this->once())->method('create')->willReturn($page);
        $this->assertEquals($page, $this->controller->execute());
    }
}
