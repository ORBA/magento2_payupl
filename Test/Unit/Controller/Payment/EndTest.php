<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class EndTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var End
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $successValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->successValidator = $this->getMockBuilder(\Magento\Checkout\Model\Session\SuccessValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()->setMethods(['getLastOrderId'])->getMock();
        $this->session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->disableOriginalConstructor()
            ->setMethods(['getLastOrderId', 'setLastOrderId'])->getMock();
        $this->clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->context->expects($this->once())->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);
        $this->orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()
            ->getMock();
        $this->controller = $this->objectManager->getObject(End::class, [
            'context' => $this->context,
            'successValidator' => $this->successValidator,
            'checkoutSession' => $this->checkoutSession,
            'session' => $this->session,
            'clientFactory' => $this->clientFactory,
            'orderHelper' => $this->orderHelper,
            'logger' => $this->logger
        ]);
    }

    public function testRedirectToHome()
    {
        $this->successValidator->expects($this->once())->method('isValid')->willReturn(false);
        $this->session->expects($this->once())->method('getLastOrderId')->willReturn(null);
        $resultRedirect = $this->getRedirectMock('/');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToCheckoutSuccess()
    {
        $this->preTestRedirectAfterPayuplResponse(false, true, true);
        $resultRedirect = $this->getRedirectMock('checkout/onepage/success');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToPayuplError()
    {
        $this->preTestRedirectAfterPayuplResponse(false, false, false);
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/error');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToPayuplErrorClient()
    {
        $this->preTestRedirectAfterPayuplResponse(false, true, false);
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/error');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToPayuplErrorClientException()
    {
        $exception = $this->preTestRedirectAfterPayuplResponse(true, true, false);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/error');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToRepeatPaymentSuccess()
    {
        $this->preTestRedirectAfterPayuplResponse(false, true, true, true);
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/repeat_success');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToRepeatPaymentError()
    {
        $this->preTestRedirectAfterPayuplResponse(false, false, true, true);
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/repeat_error');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToRepeatPaymentErrorClient()
    {
        $this->preTestRedirectAfterPayuplResponse(false, true, false, true);
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/repeat_error');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToRepeatPaymentErrorClientException()
    {
        $exception = $this->preTestRedirectAfterPayuplResponse(true, true, false, true);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $resultRedirect = $this->getRedirectMock('orba_payupl/payment/repeat_error');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
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

    /**
     * @param bool $clientException If client throws exception on initialization
     * @param bool $paymentSuccessCheck If payment was success (global check)
     * @param bool $clientPaymentSuccessCheck If payment was success (client-related check)
     * @param bool $isRepeat If it's repeat payment action
     * @return void|LocalizedException
     */
    protected function preTestRedirectAfterPayuplResponse(
        $clientException,
        $paymentSuccessCheck,
        $clientPaymentSuccessCheck,
        $isRepeat = false
    ) {
        $this->successValidator->expects($this->once())->method('isValid')->willReturn(!$isRepeat);
        if ($isRepeat) {
            $this->session->expects($this->once())->method('getLastOrderId')->willReturn(1);
        } else {
            $this->session->expects($this->once())->method('setLastOrderId')->with(null);
        }
        if ($clientException) {
            $exception = new LocalizedException(new Phrase('Exception'));
            $this->clientFactory->expects($this->once())->method('create')->willThrowException($exception);
            return $exception;
        } else {
            $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
            $orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
            $client->expects($this->once())->method('getOrderHelper')->willReturn($orderHelper);
            $this->clientFactory->expects($this->once())->method('create')->willReturn($client);
            $this->orderHelper->expects($this->once())->method('paymentSuccessCheck')->willReturn($paymentSuccessCheck);
            if ($paymentSuccessCheck) {
                $orderHelper->expects($this->once())->method('paymentSuccessCheck')
                    ->willReturn($clientPaymentSuccessCheck);
            }
        }
    }
}
