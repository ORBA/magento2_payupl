<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    const EXEMPLARY_MERCHANT_POS_ID = '145227';
    const EXEMPLARY_SIGNATURE_KEY = '13a980d4f851f3d9a1cfc792fb1f5e50';

    /**
     * @var Config
     */
    protected $_model;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $this->_model = $objectManagerHelper->getObject(
            Config::class,
            [
                'scopeConfig' => $this->_scopeConfig
            ]
        );
    }

    public function testGetConfig()
    {
        $config = $this->_model->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('merchant_pos_id', $config);
        $this->assertArrayHasKey('signature_key', $config);
    }

    public function testSetConfigSuccess()
    {
        $merchantPosId = self::EXEMPLARY_MERCHANT_POS_ID;
        $signatureKey = self::EXEMPLARY_SIGNATURE_KEY;
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->willReturn($merchantPosId);
        $this->_scopeConfig->expects($this->at(1))->method('getValue')->willReturn($signatureKey);
        $this->assertTrue($this->_model->setConfig());
        $this->assertEquals($this->_model->getConfig(), [
            'merchant_pos_id' => $merchantPosId,
            'signature_key' => $signatureKey
        ]);
    }

    public function testSetConfigMerchantPosIdEmpty()
    {
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->willReturn('');
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'Merchant POS ID is empty.');
        $this->_model->setConfig();
    }

    public function testSetConfigSignatureKeyEmpty()
    {
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->willReturn(self::EXEMPLARY_MERCHANT_POS_ID);
        $this->_scopeConfig->expects($this->at(1))->method('getValue')->willReturn('');
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'Signature key is empty.');
        $this->_model->setConfig();
    }
}