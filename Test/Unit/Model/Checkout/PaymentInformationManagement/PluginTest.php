<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Checkout\PaymentInformationManagement;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Plugin
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['setPaytype'])
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(Plugin::class, [
            'session' => $this->session
        ]);
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderNotPayupl()
    {
        $paymentInformationManagement = $this->getPaymentInformationManagemenetMock();
        $paymentMethod = $this->getPaymentMEthodMock();
        $paymentMethod->expects($this->once())->method('getMethod')->willReturn('checkmo');
        $this->session->expects($this->never())->method('setPaytype');
        $this->model->beforeSavePaymentInformationAndPlaceOrder($paymentInformationManagement, 1, $paymentMethod);
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderNoPaytype()
    {
        $paymentInformationManagement = $this->getPaymentInformationManagemenetMock();
        $paymentMethod = $this->getPaymentMEthodMock();
        $paymentMethod->expects($this->once())->method('getMethod')->willReturn(\Orba\Payupl\Model\Payupl::CODE);
        $paymentMethod->expects($this->once())->method('getAdditionalData')->willReturn([]);
        $this->session->expects($this->once())->method('setPaytype')->with($this->equalTo(null));
        $this->model->beforeSavePaymentInformationAndPlaceOrder($paymentInformationManagement, 1, $paymentMethod);
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderSuccess()
    {
        $paymentInformationManagement = $this->getPaymentInformationManagemenetMock();
        $paymentMethod = $this->getPaymentMEthodMock();
        $paymentMethod->expects($this->once())->method('getMethod')->willReturn(\Orba\Payupl\Model\Payupl::CODE);
        $paymentMethod->expects($this->once())->method('getAdditionalData')->willReturn(['paytype' => 't']);
        $this->session->expects($this->once())->method('setPaytype')->with($this->equalTo('t'));
        $this->model->beforeSavePaymentInformationAndPlaceOrder($paymentInformationManagement, 1, $paymentMethod);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPaymentInformationManagemenetMock()
    {
        return $this->getMockBuilder(\Magento\Checkout\Model\PaymentInformationManagement::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPaymentMEthodMock()
    {
        return $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentInterface::class)->getMock();
    }
}
