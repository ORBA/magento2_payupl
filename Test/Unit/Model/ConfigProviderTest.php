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

    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_paymentHelper = $this->getMockBuilder(\Magento\Payment\Helper\Data::class)->disableOriginalConstructor()->getMock();
        $this->_model = $this->_objectManager->getObject(ConfigProvider::class, [
            'paymentHelper' => $this->_paymentHelper
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
        $expectedConfig = [
            'payment' => [
                'orbaPayupl' => [
                    'redirectUrl' => $redirectUrl
                ]
            ]
        ];
        $paymentMethodMock = $this->_getPaymentMethodMock();
        $paymentMethodMock->expects($this->once())->method('isAvailable')->willReturn(true);
        $paymentMethodMock->expects($this->once())->method('getCheckoutRedirectUrl')->willReturn($redirectUrl);
        $this->_paymentHelper->expects($this->once())->method('getMethodInstance')->with($this->equalTo('orba_payupl'))->willReturn($paymentMethodMock);
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