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
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configHelper;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Classic\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(PaytypesClient::class, [
            'configHelper' => $this->configHelper
        ]);
    }

    public function testHttpClientConstruction()
    {
        $client = $this->model->getClient();
        $this->assertInstanceOf(\Zend\Http\Client::class, $client);
        $this->assertInstanceOf(\Zend\Http\Client\Adapter\Curl::class, $client->getAdapter());
    }

    public function testGetUri()
    {
        $posId = 123456;
        $keyMd5 = 'abcdef';
        $url = 'https://secure.payu.com/paygw/UTF/xml/' . $posId . '/' . substr($keyMd5, 0, 2) . '/paytype.xml';
        $this->configHelper->expects($this->at(0))->method('getConfig')->with('pos_id')->willReturn($posId);
        $this->configHelper->expects($this->at(1))->method('getConfig')->with('key_md5')->willReturn($keyMd5);
        $this->assertEquals($url, $this->model->getClient()->getUri());
    }
}
