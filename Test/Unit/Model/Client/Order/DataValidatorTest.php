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

    /**
     * @var array
     */
    protected $_exemplaryBasicData = [
        'description' => 'New order',
        'currencyCode' => 'PLN',
        'totalAmount' => 999,
        'extOrderId' => '10000001',
        'products' => [[]]
    ];

    /**
     * @var array
     */
    protected $_exemplaryProductData = [
        'name' => 'Product',
        'unitPrice' => 999,
        'quantity' => 1.5
    ];

    /**
     * @var array
     */
    protected $_exemplaryStatusUpdateData = [
        'orderId' => '123456',
        'orderStatus' => 'COMPLETED'
    ];

    /**
     * @var array
     */
    protected $_validStatusUpdateOrderStatuses = [
        'COMPLETED',
        'REJECTED'
    ];

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client\Order\DataValidator::class,
            []
        );
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

    public function testValidateProductsDataFailInvalidQuantity()
    {
        $data = $this->_getExemplaryProductsData();
        $data['products'][0]['quantity'] = 'string';
        $this->assertFalse($this->_model->validateProductsData($data));
    }

    public function testValidateProductsDataFailMissingData()
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

    public function testValidateStatusUpdateDataSuccess()
    {
        $data = $this->_getExemplaryStatusUpdateData();
        $this->assertTrue($this->_model->validateStatusUpdateData($data));
    }

    public function testValidateStatusUpdateDataFailMissingKey()
    {
        $data = $this->_getExemplaryStatusUpdateData();
        $failCount = 0;
        foreach ($data as $key => $value) {
            $missingData = array_diff_key($data, [$key => $value]);
            if (!$this->_model->validateStatusUpdateData($missingData)) {
                $failCount++;
            }
        }
        $this->assertEquals($failCount, count($data));
    }

    public function testValidateStatusUpdateDataOrderStatus()
    {
        $data = $this->_getExemplaryStatusUpdateData();
        $validStatuses = $this->_getValidStatusUpdateOrderStatuses();
        foreach ($validStatuses as $validStatus) {
            $data['orderStatus'] = $validStatus;
            $this->assertTrue($this->_model->validateStatusUpdateData($data));
        }
        $data['orderStatus'] = 'INVALID_STATUS';
        $this->assertFalse($this->_model->validateStatusUpdateData($data));
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

    /**
     * @return array
     */
    protected function _getExemplaryStatusUpdateData()
    {
        return $this->_exemplaryStatusUpdateData;
    }

    /**
     * @return array
     */
    protected function _getValidStatusUpdateOrderStatuses()
    {
        return $this->_validStatusUpdateOrderStatuses;
    }
}