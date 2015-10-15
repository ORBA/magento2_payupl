<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\MethodCaller;

class PaytypesClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaytypesClient
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configHelper;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Classic\Config::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(PaytypesClient::class, [
            'configHelper' => $this->_configHelper
        ]);
    }

    public function testHttpClientConstruction()
    {
        $client = $this->_model->getClient();
        $this->assertInstanceOf(\Zend\Http\Client::class, $client);
        $this->assertInstanceOf(\Zend\Http\Client\Adapter\Curl::class, $client->getAdapter());
    }

    public function testGetUri()
    {
        $posId = 123456;
        $keyMd5 = 'abcdef';
        $url = 'https://secure.payu.com/paygw/UTF/xml/' . $posId . '/' . substr($keyMd5, 0, 2) . '/paytype.xml';
        $this->_configHelper->expects($this->at(0))->method('getConfig')->with('pos_id')->willReturn($posId);
        $this->_configHelper->expects($this->at(1))->method('getConfig')->with('key_md5')->willReturn($keyMd5);
        $this->assertEquals($url, $this->_model->getClient()->getUri());
    }
    
}