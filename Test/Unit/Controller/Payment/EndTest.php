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
    protected $_checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderHelper;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_successValidator = $this->getMockBuilder(\Magento\Checkout\Model\Session\SuccessValidator::class)->disableOriginalConstructor()->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()->setMethods(['getLastOrderId'])->getMock();
        $this->_session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->disableOriginalConstructor()->setMethods(['getLastOrderId', 'setLastOrderId'])->getMock();
        $this->_client = $this->getMockBuilder(\Orba\Payupl\Model\ClientInterface::class)->disableOriginalConstructor()->getMock();
        $this->_context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->_controller = $this->_objectManager->getObject(End::class, [
            'context' => $this->_context,
            'successValidator' => $this->_successValidator,
            'checkoutSession' => $this->_checkoutSession,
            'session' => $this->_session,
            'client' => $this->_client,
            'orderHelper' => $this->_orderHelper
        ]);
    }

    public function testRedirectToHome()
    {
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(false);
        $this->_session->expects($this->once())->method('getLastOrderId')->willReturn(null);
        $resultRedirect = $this->_getRedirectMock('/');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToCheckoutSuccess()
    {
        $this->_preTestRedirectAfterPayuplResponse(true, true);
        $resultRedirect = $this->_getRedirectMock('checkout/onepage/success');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToPayuplError()
    {
        $this->_preTestRedirectAfterPayuplResponse(false, false);
        $resultRedirect = $this->_getRedirectMock('orba_payupl/payment/error');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToPayuplErrorClient()
    {
        $this->_preTestRedirectAfterPayuplResponse(true, false);
        $resultRedirect = $this->_getRedirectMock('orba_payupl/payment/error');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToRepeatPaymentSuccess()
    {
        $this->_preTestRedirectAfterPayuplResponse(true, true, true);
        $resultRedirect = $this->_getRedirectMock('orba_payupl/payment/repeat_success');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToRepeatPaymentError()
    {
        $this->_preTestRedirectAfterPayuplResponse(false, true, true);
        $resultRedirect = $this->_getRedirectMock('orba_payupl/payment/repeat_error');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToRepeatPaymentErrorClient()
    {
        $this->_preTestRedirectAfterPayuplResponse(true, false, true);
        $resultRedirect = $this->_getRedirectMock('orba_payupl/payment/repeat_error');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
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

    /**
     * @param bool $paymentSuccessCheck
     * @param bool $isRepeat
     */
    protected function _preTestRedirectAfterPayuplResponse($paymentSuccessCheck, $clientPaymentSuccessCheck, $isRepeat = false)
    {
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(!$isRepeat);
        if ($isRepeat) {
            $this->_session->expects($this->once())->method('getLastOrderId')->willReturn(1);
        } else {
            $this->_session->expects($this->once())->method('setLastOrderId')->with(null);
        }
        $orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $this->_client->expects($this->once())->method('getOrderHelper')->willReturn($orderHelper);
        $this->_orderHelper->expects($this->once())->method('paymentSuccessCheck')->willReturn($paymentSuccessCheck);
        if ($paymentSuccessCheck) {
            $orderHelper->expects($this->once())->method('paymentSuccessCheck')->willReturn($clientPaymentSuccessCheck);
        }
    }
}