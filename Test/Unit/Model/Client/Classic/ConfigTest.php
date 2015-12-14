<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Orba\Payupl\Model\Payupl;
use Orba\Payupl\Model\Client\Exception;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    public function testGetConfigWhole()
    {
        $result = $this->model->getConfig();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('pos_id', $result);
        $this->assertArrayHasKey('key_md5', $result);
        $this->assertArrayHasKey('second_key_md5', $result);
        $this->assertArrayHasKey('pos_auth_key', $result);
    }

    public function testGetConfigByKey()
    {
        $key = 'pos_id';
        $resultWhole = $this->model->getConfig();
        $result = $this->model->getConfig($key);
        $this->assertEquals($resultWhole[$key], $result);
    }

    public function testSetConfigSuccess()
    {
        $posId = '123456';
        $keyMd5 = 'ABC';
        $secondKeyMd5 = 'DEF';
        $posAuthKey = 'GHI';
        $this->scopeConfig->expects($this->at(0))->method('getValue')->with(
            $this->equalTo(Payupl::XML_PATH_POS_ID),
            $this->equalTo('store')
        )->willReturn($posId);
        $this->scopeConfig->expects($this->at(1))->method('getValue')->with(
            $this->equalTo(Payupl::XML_PATH_KEY_MD5),
            $this->equalTo('store')
        )->willReturn($keyMd5);
        $this->scopeConfig->expects($this->at(2))->method('getValue')->with(
            $this->equalTo(Payupl::XML_PATH_SECOND_KEY_MD5),
            $this->equalTo('store')
        )->willReturn($secondKeyMd5);
        $this->scopeConfig->expects($this->at(3))->method('getValue')->with(
            $this->equalTo(Payupl::XML_PATH_POS_AUTH_KEY),
            $this->equalTo('store')
        )->willReturn($posAuthKey);
        $this->assertTrue($this->model->setConfig());
        $this->assertEquals($this->model->getConfig(), [
            'pos_id' => $posId,
            'key_md5' => $keyMd5,
            'second_key_md5' => $secondKeyMd5,
            'pos_auth_key' => $posAuthKey
        ]);
    }

    public function testSetConfigPosIdEmpty()
    {
        $this->scopeConfig->expects($this->at(0))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_POS_ID), $this->equalTo('store'))->willReturn('');
        $this->setExpectedException(Exception::class, 'POS ID is empty.');
        $this->model->setConfig();
    }

    public function testSetConfigKeyMd5Empty()
    {
        $this->scopeConfig->expects($this->at(0))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_POS_ID), $this->equalTo('store'))->willReturn('value');
        $this->scopeConfig->expects($this->at(1))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_KEY_MD5), $this->equalTo('store'))->willReturn('');
        $this->setExpectedException(Exception::class, 'Key MD5 is empty.');
        $this->model->setConfig();
    }

    public function testSetConfigSecondKeyMd5Empty()
    {
        $this->scopeConfig->expects($this->at(0))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_POS_ID), $this->equalTo('store'))->willReturn('value');
        $this->scopeConfig->expects($this->at(1))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_KEY_MD5), $this->equalTo('store'))->willReturn('value');
        $this->scopeConfig->expects($this->at(2))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_SECOND_KEY_MD5), $this->equalTo('store'))->willReturn('');
        $this->setExpectedException(Exception::class, 'Second key MD5 is empty.');
        $this->model->setConfig();
    }

    public function testSetConfigPosAuthKeyEmpty()
    {
        $this->scopeConfig->expects($this->at(0))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_POS_ID), $this->equalTo('store'))->willReturn('value');
        $this->scopeConfig->expects($this->at(1))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_KEY_MD5), $this->equalTo('store'))->willReturn('value');
        $this->scopeConfig->expects($this->at(2))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_SECOND_KEY_MD5), $this->equalTo('store'))->willReturn('value');
        $this->scopeConfig->expects($this->at(3))->method('getValue')
            ->with($this->equalTo(Payupl::XML_PATH_POS_AUTH_KEY), $this->equalTo('store'))->willReturn('');
        $this->setExpectedException(Exception::class, 'POS auth key is empty.');
        $this->model->setConfig();
    }
}
