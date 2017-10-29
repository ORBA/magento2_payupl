<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment\Repeat;

class SuccessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Success
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)
            ->setMethods(['getLastOrderId', 'setLastOrderId'])->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);
        $this->controller = $objectManager->getObject(Success::class, [
            'context' => $context,
            'resultPageFactory' => $this->resultPageFactory,
            'session' => $this->session
        ]);
    }

    public function testRedirectToHomeOnInvalidSession()
    {
        $this->session->expects($this->once())->method('getLastOrderId')->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('/');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testExecute()
    {
        $this->session->expects($this->once())->method('getLastOrderId')->willReturn(1);
        $this->session->expects($this->once())->method('setLastOrderId')->with(null);
        $title = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)->disableOriginalConstructor()
            ->getMock();
        $title->expects($this->once())->method('set')->with(__('Thank you for your payment!'));
        $config = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->once())->method('getTitle')->willReturn($title);
        $page = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)->disableOriginalConstructor()
            ->getMock();
        $page->expects($this->once())->method('getConfig')->willReturn($config);
        $this->resultPageFactory->expects($this->once())->method('create')->willReturn($page);
        $this->assertEquals($page, $this->controller->execute());
    }
}
