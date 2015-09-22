<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
    protected $_orderHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_client;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_successValidator = $this->getMockBuilder(\Magento\Checkout\Model\Session\SuccessValidator::class)->disableOriginalConstructor()->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_session = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()->setMethods(['getLastOrderId'])->getMock();
        $this->_client = $this->getMockBuilder(\Orba\Payupl\Model\ClientInterface::class)->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_controller = $this->_objectManager->getObject(Start::class, [
            'context' => $context,
            'successValidator' => $this->_successValidator,
            'session' => $this->_session,
            'client' => $this->_client
        ]);
    }

    public function testRedirectToCartOnInvalidCheckout()
    {
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(false);
        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('checkout/cart');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToPayupl()
    {
        $orderId = '123';
        $orderData = ['extOrderId' => '0000000001-1'];
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(true);
        $this->_session->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $this->_client->expects($this->once())->method('getOrderHelper')->willReturn($orderHelper);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $orderHelper->expects($this->once())->method('loadOrderById')->with($this->equalTo($orderId))->willReturn($order);
        $orderHelper->expects($this->once())->method('getDataForOrderCreate')->with($this->equalTo($order))->willReturn($orderData);
        $response = [
            'redirectUri' => 'http://redirect.url',
            'orderId' => 'Z963D5JQR2230925GUEST000P01',
            'extOrderId' => $orderData['extOrderId']
        ];
        $this->_client->expects($this->once())->method('orderCreate')->with($this->equalTo($orderData))->willReturn($response);
        $orderHelper->expects($this->once())->method('saveNewTransaction')->with(
            $this->equalTo($orderId),
            $this->equalTo($response['orderId']),
            $this->equalTo($response['extOrderId'])
        );
        $orderHelper->expects($this->once())->method('setNewOrderStatus')->with($this->equalTo($order));
        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with($response['redirectUri']);
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }
}