<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Checkout\PaymentInformationManagement;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Plugin
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['setPaytype'])->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Plugin::class, [
            'session' => $this->_session
        ]);
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderNotPayupl()
    {
        $paymentInformationManagement = $this->_getPaymentInformationManagemenetMock();
        $paymentMethod = $this->_getPaymentMEthodMock();
        $paymentMethod->expects($this->once())->method('getMethod')->willReturn('checkmo');
        $this->_session->expects($this->never())->method('setPaytype');
        $this->_model->beforeSavePaymentInformationAndPlaceOrder($paymentInformationManagement, 1, $paymentMethod);
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderNoPaytype()
    {
        $paymentInformationManagement = $this->_getPaymentInformationManagemenetMock();
        $paymentMethod = $this->_getPaymentMEthodMock();
        $paymentMethod->expects($this->once())->method('getMethod')->willReturn(\Orba\Payupl\Model\Payupl::CODE);
        $paymentMethod->expects($this->once())->method('getAdditionalData')->willReturn([]);
        $this->_session->expects($this->once())->method('setPaytype')->with($this->equalTo(null));
        $this->_model->beforeSavePaymentInformationAndPlaceOrder($paymentInformationManagement, 1, $paymentMethod);
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderSuccess()
    {
        $paymentInformationManagement = $this->_getPaymentInformationManagemenetMock();
        $paymentMethod = $this->_getPaymentMEthodMock();
        $paymentMethod->expects($this->once())->method('getMethod')->willReturn(\Orba\Payupl\Model\Payupl::CODE);
        $paymentMethod->expects($this->once())->method('getAdditionalData')->willReturn(['paytype' => 't']);
        $this->_session->expects($this->once())->method('setPaytype')->with($this->equalTo('t'));
        $this->_model->beforeSavePaymentInformationAndPlaceOrder($paymentInformationManagement, 1, $paymentMethod);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getPaymentInformationManagemenetMock()
    {
        return $this->getMockBuilder(\Magento\Checkout\Model\PaymentInformationManagement::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getPaymentMEthodMock()
    {
        return $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentInterface::class)->getMock();
    }
}