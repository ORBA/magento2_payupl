<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class EndTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var End
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_successValidator;

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
    protected $_client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_successValidator = $this->getMockBuilder(\Magento\Checkout\Model\Session\SuccessValidator::class)->disableOriginalConstructor()->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_session = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()->setMethods(['getLastOrderId'])->getMock();
        $this->_client = $this->getMockBuilder(\Orba\Payupl\Model\ClientInterface::class)->disableOriginalConstructor()->getMock();
        $this->_context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_controller = $this->_objectManager->getObject(End::class, [
            'context' => $this->_context,
            'successValidator' => $this->_successValidator,
            'session' => $this->_session,
            'client' => $this->_client
        ]);
    }

    public function testRedirectToCartOnInvalidCheckout()
    {
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(false);
        $resultRedirect = $this->_getRedirectMock('checkout/cart');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToCheckoutSuccess()
    {
        $this->_preTestRedirectAfterPayuplResponse(true);
        $resultRedirect = $this->_getRedirectMock('checkout/onepage/success');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToPayuplError()
    {
        $this->_preTestRedirectAfterPayuplResponse(false);
        $resultRedirect = $this->_getRedirectMock('orba_payupl/payment/error');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    /**
     * @param string $path
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getRedirectMock($path)
    {
        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with($path);
        return $resultRedirect;
    }

    /**
     * @param bool $paymentSuccessCheck
     */
    protected function _preTestRedirectAfterPayuplResponse($paymentSuccessCheck)
    {
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(true);
        $orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $this->_client->expects($this->once())->method('getOrderHelper')->willReturn($orderHelper);
        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $this->_context->expects($this->once())->method('getRequest')->willReturn($request);
        $orderHelper->expects($this->once())->method('paymentSuccessCheck')->with($request)->willReturn($paymentSuccessCheck);
    }
}