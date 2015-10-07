<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Orba\Payupl\Model\Client\Exception;

class StartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Start
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resultRedirectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->_session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['setLastOrderId'])->disableOriginalConstructor()->getMock();
        $this->_controller = $this->_objectManager->getObject(Start::class, [
            'context' => $context,
            'clientFactory' => $this->_clientFactory,
            'orderHelper' => $this->_orderHelper,
            'session' => $this->_session
        ]);
    }

    public function testRedirectToCartOnInvalidOrderId()
    {
        $this->_orderHelper->expects($this->once())->method('getOrderIdForPaymentStart')->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('checkout/cart');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToCheckoutOnInvalidOrder()
    {
        $orderId = 123;
        $this->_orderHelper->expects($this->once())->method('getOrderIdForPaymentStart')->willReturn($orderId);
        $order = $this->_getOrderMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('checkout/cart');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToErrorOnClientException()
    {
        $orderId = 123;
        $orderData = ['extOrderId' => '0000000001-1'];
        $this->_orderHelper->expects($this->once())->method('getOrderIdForPaymentStart')->willReturn($orderId);
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $client = $this->_getClientMock();
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $order = $this->_getOrderMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))->willReturn(true);
        $clientOrderHelper->expects($this->once())->method('getDataForOrderCreate')->with($this->equalTo($order))->willReturn($orderData);
        $response = [
            'redirectUri' => 'http://redirect.url',
            'orderId' => 'Z963D5JQR2230925GUEST000P01',
            'extOrderId' => $orderData['extOrderId']
        ];
        $exception = new Exception();
        $client->expects($this->once())->method('orderCreate')->with($this->equalTo($orderData))->willThrowException($exception);
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with(
            $this->equalTo('orba_payupl/payment/end'),
            $this->equalTo(['exception' => '1'])
        );
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->_session->expects($this->once())->method('setLastOrderId')->with($this->equalTo($orderId));
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToPayupl()
    {
        $orderId = 123;
        $orderData = ['extOrderId' => '0000000001-1'];
        $this->_orderHelper->expects($this->once())->method('getOrderIdForPaymentStart')->willReturn($orderId);
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $client = $this->_getClientMock();
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $order = $this->_getOrderMock();
        $this->_orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn($order);
        $this->_orderHelper->expects($this->once())->method('canStartFirstPayment')->with($this->equalTo($order))->willReturn(true);
        $clientOrderHelper->expects($this->once())->method('getDataForOrderCreate')->with($this->equalTo($order))->willReturn($orderData);
        $response = [
            'redirectUri' => 'http://redirect.url',
            'orderId' => 'Z963D5JQR2230925GUEST000P01',
            'extOrderId' => $orderData['extOrderId']
        ];
        $client->expects($this->once())->method('orderCreate')->with($this->equalTo($orderData))->willReturn($response);
        $status = 'status';
        $clientOrderHelper->expects($this->once())->method('getNewStatus')->willReturn($status);
        $this->_orderHelper->expects($this->once())->method('addNewOrderTransaction')->with(
            $this->equalTo($order),
            $this->equalTo($response['orderId']),
            $this->equalTo($response['extOrderId']),
            $this->equalTo($status)
        );
        $this->_orderHelper->expects($this->once())->method('setNewOrderStatus')->with($this->equalTo($order));
        $this->_session->expects($this->once())->method('setLastOrderId')->with($this->equalTo($orderId));
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with($response['redirectUri']);
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock()
    {
        return $this->getMockBuilder(\Orba\Payupl\Model\Sales\Order::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->_clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }
}