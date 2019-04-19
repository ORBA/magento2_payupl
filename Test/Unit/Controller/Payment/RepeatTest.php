<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

class RepeatTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var Repeat
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);
        $this->paymentHelper = $this->getMockBuilder(\Orba\Payupl\Helper\Payment::class)
            ->setMethods(['getOrderIdIfCanRepeat'])->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
        $this->session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['setLastOrderId'])
            ->disableOriginalConstructor()->getMock();
        $this->controller = $objectManager->getObject(Repeat::class, [
            'context' => $this->context,
            'paymentHelper' => $this->paymentHelper,
            'session' => $this->session
        ]);
    }

    public function testInvalidLink()
    {
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/repeat_error');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $request->expects($this->once())->method('getParam')->with('id');
        $this->context->expects($this->once())->method('getRequest')->willReturn($request);
        $this->paymentHelper->expects($this->once())->method('getOrderIdIfCanRepeat')->willReturn(false);
        $this->messageManager->expects($this->once())->method('addError')
            ->with(__('The repeat payment link is invalid.'));
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testSuccess()
    {
        $id = 'QUJD'; // base64_decode('ABC');
        $payuplOrderId = 'ABC';
        $orderId = 1;
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/repeat_start');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $request->expects($this->once())->method('getParam')->with('id')->willReturn($id);
        $this->context->expects($this->once())->method('getRequest')->willReturn($request);
        $this->paymentHelper->expects($this->once())->method('getOrderIdIfCanRepeat')->with($payuplOrderId)
            ->willReturn($orderId);
        $this->session->expects($this->once())->method('setLastOrderId')->with($orderId);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    /**
     * @param string $path
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRedirectMock($path)
    {
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with($path);
        return $resultRedirect;
    }
}
