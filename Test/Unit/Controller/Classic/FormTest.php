<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Classic;

class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Form
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resultRedirectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resultPageFactory;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['getOrderCreateData', 'setOrderCreateData'])->disableOriginalConstructor()->getMock();
        $this->_controller = $objectManager->getObject(Form::class, [
            'context' => $context,
            'session' => $this->_session,
            'resultPageFactory' => $this->_resultPageFactory
        ]);
    }

    public function testRedirectToHomeOnInvalidSession()
    {
        $this->_session->expects($this->once())->method('getOrderCreateData')->willReturn(null);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('/');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testSuccess()
    {
        $data = ['data'];
        $this->_session->expects($this->once())->method('getOrderCreateData')->willReturn($data);
        $this->_session->expects($this->once())->method('setOrderCreateData')->with($this->equalTo(null));
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\Template::class)->setMethods(['setOrderCreateData'])->disableOriginalConstructor()->getMock();
        $block->expects($this->once())->method('setOrderCreateData')->with($this->equalTo($data));
        $layout = $this->getMockBuilder(\Magento\Framework\View\Layout::class)->disableOriginalConstructor()->getMock();
        $layout->expects($this->once())->method('getBlock')->with($this->equalTo('orba.payupl.classic.form'))->willReturn($block);
        $page = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)->disableOriginalConstructor()->getMock();
        $defaultLayoutHandle = 'handle';
        $page->expects($this->once())->method('getDefaultLayoutHandle')->willReturn($defaultLayoutHandle);
        $page->expects($this->once())->method('addHandle')->with($this->equalTo($defaultLayoutHandle));
        $page->expects($this->once())->method('getLayout')->willReturn($layout);
        $this->_resultPageFactory->expects($this->once())->method('create')->with(
            $this->equalTo(true),
            $this->equalTo(['template' => 'Orba_Payupl::emptyroot.phtml'])
        )->willReturn($page);
        $this->assertEquals($page, $this->_controller->execute());
    }
}