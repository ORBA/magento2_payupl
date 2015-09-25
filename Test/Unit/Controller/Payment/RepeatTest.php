<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

class RepeatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resultRedirectFactory;

    /**
     * @var Repeat
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paymentHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_paymentHelper = $this->getMockBuilder(\Orba\Payupl\Helper\Payment::class)->setMethods(['getOrderIdIfCanRepeat'])->disableOriginalConstructor()->getMock();
        $this->_messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMockForAbstractClass();
        $this->_context->expects($this->once())->method('getMessageManager')->willReturn($this->_messageManager);
        $this->_session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['setLastOrderId'])->disableOriginalConstructor()->getMock();
        $this->_controller = $objectManager->getObject(Repeat::class, [
            'context' => $this->_context,
            'paymentHelper' => $this->_paymentHelper,
            'session' => $this->_session
        ]);
    }

    public function testInvalidLink()
    {
        $resultRedirect = $this->_getRedirectMock('orba_payupl/payment/repeat_error');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $request->expects($this->once())->method('getParam')->with('id');
        $this->_context->expects($this->once())->method('getRequest')->willReturn($request);
        $this->_paymentHelper->expects($this->once())->method('getOrderIdIfCanRepeat')->willReturn(false);
        $this->_messageManager->expects($this->once())->method('addError')->with(__('The repeat payment link is invalid.'));
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testSuccess()
    {
        $payuplOrderId = 'ABC';
        $orderId = 1;
        $resultRedirect = $this->_getRedirectMock('orba_payupl/payment/repeat_start');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $request->expects($this->once())->method('getParam')->with('id')->willReturn($payuplOrderId);
        $this->_context->expects($this->once())->method('getRequest')->willReturn($request);
        $this->_paymentHelper->expects($this->once())->method('getOrderIdIfCanRepeat')->with($payuplOrderId)->willReturn($orderId);
        $this->_session->expects($this->once())->method('setLastOrderId')->with($orderId);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    /**
     * @param string $path
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getRedirectMock($path)
    {
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with($path);
        return $resultRedirect;
    }
}