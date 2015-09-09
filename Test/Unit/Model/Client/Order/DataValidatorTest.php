<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client\Order\DataValidator
     */
    protected $_model;

    protected $_exemplaryBasicData = [
        'description' => 'New order',
        'currencyCode' => 'PLN',
        'totalAmount' => 999,
        'extOrderId' => '10000001',
        'products' => [[]]
    ];
    protected $_exemplaryProductData = [
        'name' => 'Product',
        'unitPrice' => 999,
        'quantity' => 1
    ];

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client\Order\DataValidator::class,
            []
        );
    }

    public function testValidateEmpty()
    {
        $this->assertFalse($this->_model->validateEmpty(null));
        $this->assertFalse($this->_model->validateEmpty(false));
        $this->assertFalse($this->_model->validateEmpty(''));
        $this->assertFalse($this->_model->validateEmpty([]));
        $this->assertTrue($this->_model->validateEmpty(true));
        $this->assertTrue($this->_model->validateEmpty('notempty'));
        $this->assertTrue($this->_model->validateEmpty(['notempty' => true]));
    }

    public function testValidateBasicDataSuccess()
    {
        $data = $this->_getExemplaryBasicData();
        $this->assertTrue($this->_model->validateBasicData($data));
    }

    public function testValidateBasicDataFail()
    {
        $data = $this->_getExemplaryBasicData();
        $failCount = 0;
        foreach ($data as $key => $value) {
            $missingData = array_diff_key($data, [$key => $value]);
            if (!$this->_model->validateBasicData($missingData)) {
                $failCount++;
            }
        }
        $this->assertEquals($failCount, count($data));
    }

    public function testValidateProductsDataSuccess()
    {
        $data = $this->_getExemplaryProductsData();
        $this->assertTrue($this->_model->validateProductsData($data));
    }

    public function testValidateProductsDataFail()
    {
        $productData = $this->_getExemplaryProductData();
        $failCount = 0;
        foreach ($productData as $key => $value) {
            $missingProductData = array_diff_key($productData, [$key => $value]);
            $missingData = [
                'products' => [
                    $missingProductData
                ]
            ];
            if (!$this->_model->validateProductsData($missingData)) {
                $failCount++;
            }
        }
        $this->assertEquals($failCount, count($productData));
    }

    /**
     * @return array
     */
    protected function _getExemplaryBasicData()
    {
        return $this->_exemplaryBasicData;
    }

    /**
     * @return array
     */
    protected function _getExemplaryProductData()
    {
        return $this->_exemplaryProductData;
    }

    /**
     * @return array
     */
    protected function _getExemplaryProductsData()
    {
        $data = [
            'products' => [
                $this->_getExemplaryProductData()
            ]
        ];
        return $data;
    }
}