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

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $context = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_successValidator = $this->getMockBuilder(\Magento\Checkout\Model\Session\SuccessValidator::class)->disableOriginalConstructor()->getMock();
        $this->_resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->_session = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()->setMethods(['getLastOrderId'])->getMock();
        $this->_client = $this->getMockBuilder(\Orba\Payupl\Model\ClientInterface::class)->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResultRedirectFactory')->willReturn($this->_resultRedirectFactory);
        $this->_controller = $this->_objectManager->getObject(End::class, [
            'context' => $context,
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

    public function testRedirectToCartOnPayuplOrderIdNotFound()
    {
        $orderId = 1;
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(true);
        $this->_session->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $this->_client->expects($this->once())->method('getOrderHelper')->willReturn($orderHelper);
        $orderHelper->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->with($this->equalTo($orderId))->willReturn(false);
        $resultRedirect = $this->_getRedirectMock('checkout/cart');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToCartOnClientException()
    {
        $orderId = 1;
        $payuplOrderId = 'ABC';
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(true);
        $this->_session->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $this->_client->expects($this->once())->method('getOrderHelper')->willReturn($orderHelper);
        $orderHelper->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->with($this->equalTo($orderId))->willReturn($payuplOrderId);
        $this->_client->expects($this->once())->method('orderRetrieve')->with($this->equalTo($payuplOrderId))->willThrowException(new \Orba\Payupl\Model\Client\Exception());
        $resultRedirect = $this->_getRedirectMock('checkout/cart');
        $this->_resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $this->assertEquals($resultRedirect, $this->_controller->execute());
    }

    public function testRedirectToCheckoutSuccess()
    {
        $orderId = 1;
        $payuplOrderId = 'ABC';
        $status = 'PENDING';
        $this->_successValidator->expects($this->once())->method('isValid')->willReturn(true);
        $this->_session->expects($this->once())->method('getLastOrderId')->willReturn($orderId);
        $orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->getMock();
        $this->_client->expects($this->once())->method('getOrderHelper')->willReturn($orderHelper);
        $orderHelper->expects($this->once())->method('getLastPayuplOrderIdByOrderId')->with($this->equalTo($orderId))->willReturn($payuplOrderId);
        $this->_client->expects($this->once())->method('orderRetrieve')->with($this->equalTo($payuplOrderId))->willReturn($status);
        $orderHelper->expects($this->once())->method('canContinueCheckout')->with($this->equalTo($status))->willReturn(true);
        $resultRedirect = $this->_getRedirectMock('checkout/onepage/success');
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
}