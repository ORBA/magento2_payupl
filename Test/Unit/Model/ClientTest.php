<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Zend\Server\Reflection\ReflectionClass;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    const EXEMPLARY_MERCHANT_POS_ID = '145227';
    const EXEMPLARY_SIGNATURE_KEY = '13a980d4f851f3d9a1cfc792fb1f5e50';

    protected $_exemplaryOrderBasicData = [
        'continueUrl' => 'http://localhost/',
        'notifyUrl' => 'http://localhost/',
        'description' => 'New order',
        'currencyCode' => 'PLN',
        'totalAmount' => 999,
        'extOrderId' => '10000001'
    ];
    /**
     * @var \Orba\Payupl\Model\Client
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $this->_model = $objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client::class,
            [
                'scopeConfig' => $this->_scopeConfig
            ]
        );
    }

    public function testSetConfigUnset()
    {
        $this->_expectCorrectConfig();
        $this->assertFalse($this->_model->isConfigSet());
        $this->assertTrue($this->_model->setConfig());
        $this->assertTrue($this->_model->isConfigSet());
        $this->assertEquals($this->_model->getCurrentConfigFromSDK(), [
            'merchant_pos_id' => self::EXEMPLARY_MERCHANT_POS_ID,
            'signature_key' => self::EXEMPLARY_SIGNATURE_KEY
        ]);
    }

    public function testSetConfigAlreadySet()
    {
        $this->_scopeConfig->expects($this->exactly(0))->method('getValue');
        $reflection = new \ReflectionClass($this->_model);
        $reflectionProperty = $reflection->getProperty('_isConfigSet');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_model, true);
        $this->assertTrue($this->_model->setConfig());
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

    public function testOrderNoData()
    {
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'Order request data array is empty.');
        $this->_model->order();
    }
    
    public function testOrderBasicDataMissing()
    {
        $data = $this->_exemplaryOrderBasicData;
        $exceptionCount = 0;
        foreach ($data as $key => $value) {
            try {
                $missingData = array_diff_key($data, [$key => $value]);
                $this->_model->order($missingData);
            } catch (\Orba\Payupl\Model\Client\Exception $e) {
                if ($e->getMessage() === 'Order request data array basic element "' . $key . '" is missing.') {
                    $exceptionCount++;
                }
            }
        }
        $this->assertEquals($exceptionCount, count($data));
    }

    protected function _expectCorrectConfig()
    {
        $this->_scopeConfig->expects($this->at(0))->method('getValue')->willReturn(self::EXEMPLARY_MERCHANT_POS_ID);
        $this->_scopeConfig->expects($this->at(1))->method('getValue')->willReturn(self::EXEMPLARY_SIGNATURE_KEY);
    }

}