<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment\Repeat;

class SuccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Success
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resultPageFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)->disableOriginalConstructor()->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['getLastOrderId', 'setLastOrderId'])->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_controller = $objectManager->getObject(Success::class, [
            'context' => $context,
            'resultPageFactory' => $this->_resultPageFactory,
            'session' => $this->_session
        ]);
    }

    public function testRedirectToHomeOnInvalidSession()
    {
        $this->_session->expects($this->once())->method('getLastOrderId')->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('/');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testExecute()
    {
        $this->_session->expects($this->once())->method('getLastOrderId')->willReturn(1);
        $this->_session->expects($this->once())->method('setLastOrderId')->with(null);
        $title = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)->disableOriginalConstructor()->getMock();
        $title->expects($this->once())->method('set')->with(__('Thank you for your payment!'));
        $config = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)->disableOriginalConstructor()->getMock();
        $config->expects($this->once())->method('getTitle')->willReturn($title);
        $page = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)->disableOriginalConstructor()->getMock();
        $page->expects($this->once())->method('getConfig')->willReturn($config);
        $this->_resultPageFactory->expects($this->once())->method('create')->willReturn($page);
        $this->assertEquals($page, $this->_controller->execute());
    }
}