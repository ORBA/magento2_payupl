<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var ConfigProvider
     */
    protected $model;

    /**
     * @var Payupl
     */
    protected $paymentInstance;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paytypeHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->paymentHelper = $this->getMockBuilder(\Magento\Payment\Helper\Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->paytypeHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order\Paytype::class)
            ->disableOriginalConstructor()->getMock();
        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $this->objectManager->getObject(ConfigProvider::class, [
            'paymentHelper' => $this->paymentHelper,
            'paytypeHelper' => $this->paytypeHelper,
            'checkoutSession' => $this->checkoutSession
        ]);
    }

    public function testGetConfigUnavailable()
    {
        $paymentMethodMock = $this->getPaymentMethodMock();
        $paymentMethodMock->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->paymentHelper->expects($this->once())->method('getMethodInstance')
            ->with($this->equalTo('orba_payupl'))->willReturn($paymentMethodMock);
        $this->assertEquals([], $this->model->getConfig());
    }

    public function testGetConfigAvailable()
    {
        $redirectUrl = 'http://redirect.url';
        $paytypes = ['paytypes'];
        $expectedConfig = [
            'payment' => [
                'orbaPayupl' => [
                    'redirectUrl' => $redirectUrl,
                    'paytypes' => $paytypes
                ]
            ]
        ];
        $paymentMethodMock = $this->getPaymentMethodMock();
        $paymentMethodMock->expects($this->once())->method('isAvailable')->willReturn(true);
        $paymentMethodMock->expects($this->once())->method('getCheckoutRedirectUrl')->willReturn($redirectUrl);
        $this->paymentHelper->expects($this->once())->method('getMethodInstance')->with($this->equalTo('orba_payupl'))
            ->willReturn($paymentMethodMock);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)->getMock();
        $this->checkoutSession->expects($this->once())->method('getQuote')->willReturn($quote);
        $this->paytypeHelper->expects($this->once())->method('getAllForQuote')->with($this->equalTo($quote))
            ->willReturn($paytypes);
        $this->assertEquals($expectedConfig, $this->model->getConfig());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPaymentMethodMock()
    {
        return $this->getMockBuilder(Payupl::class)
            ->setMethods([
                'isAvailable',
                'getCheckoutRedirectUrl'
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }
}
