<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataAdderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client\Order\DataAdder
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configHelper;

    /**
     * @var \Magento\Framework\View\Context
     */
    protected $_context;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $this->_configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Config::class)->disableOriginalConstructor()->getMock();
        $this->_context = $objectManagerHelper->getObject(
            \Magento\Framework\View\Context::class,
            ['urlBuilder' => $this->_urlBuilder]
        );
        $this->_model = $objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client\Order\DataAdder::class,
            [
                'context' => $this->_context,
                'configHelper' => $this->_configHelper
            ]
        );
    }
    
    public function testContinueUrl()
    {
        $path = 'orba_payupl/payment/continue';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $this->_urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->assertEquals($url, $this->_model->getContinueUrl());
    }

    public function testNotifyUrl()
    {
        $path = 'orba_payupl/payment/notify';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $this->_urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->assertEquals($url, $this->_model->getNotifyUrl());
    }

    public function testCustomerIp()
    {
        $ip = '127.0.0.1';
        $_SERVER['REMOTE_ADDR'] = $ip;
        $this->assertEquals($ip, $this->_model->getCustomerIp());
    }

    public function testMerchantPosId()
    {
        $merchantPosId = '123456';
        $this->_configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('merchant_pos_id'))->willReturn($merchantPosId);
        $this->assertEquals($merchantPosId, $this->_model->getMerchantPosId());
    }
}