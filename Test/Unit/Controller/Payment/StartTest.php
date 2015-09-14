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
        $this->_orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->_client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_controller = $this->_objectManager->getObject(Start::class, [
            'context' => $context,
            'successValidator' => $this->_successValidator,
            'session' => $this->_session,
            'orderHelper' => $this->_orderHelper,
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
        $orderData = ['data'];
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(true);
        $this->_session->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $this->_orderHelper->expects($this->once())->method('getDataForNewTransaction')->with($this->equalTo($orderId))->willReturn($orderData);
        $result = $this->getMockBuilder(\OpenPayU_Result::class)->disableOriginalConstructor()->getMock();
        $this->_client->expects($this->once())->method('orderCreate')->with($this->equalTo($orderData))->willReturn($result);
        $response = $this->getMockBuilder(\stdClass::class)->getMock();
        $response->redirectUri = 'http://redirect.url';
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with($response->redirectUri);
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }
}