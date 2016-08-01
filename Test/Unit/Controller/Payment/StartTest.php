<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Phrase;

class StartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Start
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)
            ->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);
        $this->orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['setLastOrderId'])
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()
            ->getMock();
        $this->controller = $this->objectManager->getObject(Start::class, [
            'context' => $context,
            'clientFactory' => $this->clientFactory,
            'orderHelper' => $this->orderHelper,
            'session' => $this->session,
            'logger' => $this->logger
        ]);
    }

    public function testRedirectToCartOnInvalidOrderId()
    {
        $this->orderHelper->expects($this->once())->method('getOrderIdForPaymentStart')->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('checkout/cart');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToCheckoutOnInvalidOrder()
    {
        $orderId = 123;
        $this->orderHelper->expects($this->once())->method('getOrderIdForPaymentStart')->willReturn($orderId);
        $order = $this->getOrderMock();
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn($order);
        $this->orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))
            ->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('checkout/cart');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToErrorOnClientException()
    {
        $orderId = 123;
        $orderData = ['extOrderId' => '0000000001-1'];
        $this->orderHelper->expects($this->once())->method('getOrderIdForPaymentStart')->willReturn($orderId);
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $client = $this->getClientMock();
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $order = $this->getOrderMock();
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn($order);
        $this->orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))
            ->willReturn(true);
        $clientOrderHelper->expects($this->once())->method('getDataForOrderCreate')->with($this->equalTo($order))
            ->willReturn($orderData);
        $exception = new LocalizedException(new Phrase('Exception'));
        $client->expects($this->once())->method('orderCreate')->with($this->equalTo($orderData))
            ->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with(
            $this->equalTo('orba_payupl/payment/end'),
            $this->equalTo(['exception' => '1'])
        );
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->session->expects($this->once())->method('setLastOrderId')->with($this->equalTo($orderId));
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testRedirectToPayupl()
    {
        $orderId = 123;
        $orderData = ['extOrderId' => '0000000001-1'];
        $this->orderHelper->expects($this->once())->method('getOrderIdForPaymentStart')->willReturn($orderId);
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $client = $this->getClientMock();
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $order = $this->getOrderMock();
        $this->orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))
            ->willReturn($order);
        $this->orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))
            ->willReturn(true);
        $clientOrderHelper->expects($this->once())->method('getDataForOrderCreate')->with($this->equalTo($order))
            ->willReturn($orderData);
        $response = [
            'redirectUri' => 'http://redirect.url',
            'orderId' => 'Z963D5JQR2230925GUEST000P01',
            'extOrderId' => $orderData['extOrderId']
        ];
        $client->expects($this->once())->method('orderCreate')->with($this->equalTo($orderData))->willReturn($response);
        $status = 'status';
        $clientOrderHelper->expects($this->once())->method('getNewStatus')->willReturn($status);
        $this->orderHelper->expects($this->once())->method('addNewOrderTransaction')->with(
            $this->equalTo($order),
            $this->equalTo($response['orderId']),
            $this->equalTo($response['extOrderId']),
            $this->equalTo($status)
        );
        $this->orderHelper->expects($this->once())->method('setNewOrderStatus')->with($this->equalTo($order));
        $this->session->expects($this->once())->method('setLastOrderId')->with($this->equalTo($orderId));
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with($response['redirectUri']);
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->controller->execute());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }
}
