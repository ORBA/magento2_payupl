<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @var ConfigProvider
     */
    protected $_model;

    /**
     * @var Payupl
     */
    protected $_paymentInstance;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_paytypeHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSession;

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_paymentHelper = $this->getMockBuilder(\Magento\Payment\Helper\Data::class)->disableOriginalConstructor()->getMock();
        $this->_paytypeHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order\Paytype::class)->disableOriginalConstructor()->getMock();
        $this->_checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()->getMock();
        $this->_model = $this->_objectManager->getObject(ConfigProvider::class, [
            'paymentHelper' => $this->_paymentHelper,
            'paytypeHelper' => $this->_paytypeHelper,
            'checkoutSession' => $this->_checkoutSession
        ]);
    }

    public function testGetConfigUnavailable()
    {
        $paymentMethodMock = $this->_getPaymentMethodMock();
        $paymentMethodMock->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->_paymentHelper->expects($this->once())->method('getMethodInstance')->with($this->equalTo('orba_payupl'))->willReturn($paymentMethodMock);
        $this->assertEquals([], $this->_model->getConfig());
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
        $paymentMethodMock = $this->_getPaymentMethodMock();
        $paymentMethodMock->expects($this->once())->method('isAvailable')->willReturn(true);
        $paymentMethodMock->expects($this->once())->method('getCheckoutRedirectUrl')->willReturn($redirectUrl);
        $this->_paymentHelper->expects($this->once())->method('getMethodInstance')->with($this->equalTo('orba_payupl'))->willReturn($paymentMethodMock);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)->getMock();
        $this->_checkoutSession->expects($this->once())->method('getQuote')->willReturn($quote);
        $this->_paytypeHelper->expects($this->once())->method('getAllForQuote')->with($this->equalTo($quote))->willReturn($paytypes);
        $this->assertEquals($expectedConfig, $this->_model->getConfig());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getPaymentMethodMock()
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