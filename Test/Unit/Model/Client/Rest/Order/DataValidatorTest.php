<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataValidator
     */
    protected $model;

    /**
     * @var array
     */
    protected $exemplaryBasicData = [
        'description' => 'New order',
        'currencyCode' => 'PLN',
        'totalAmount' => 999,
        'extOrderId' => '10000001',
        'products' => [[]]
    ];

    /**
     * @var array
     */
    protected $exemplaryProductData = [
        'name' => 'Product',
        'unitPrice' => 999,
        'quantity' => 1.5
    ];

    /**
     * @var array
     */
    protected $exemplaryStatusUpdateData = [
        'orderId' => '123456',
        'orderStatus' => 'COMPLETED'
    ];

    /**
     * @var array
     */
    protected $validStatusUpdateOrderStatuses = [
        'COMPLETED',
        'REJECTED'
    ];

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            DataValidator::class,
            []
        );
    }

    public function testValidateBasicDataSuccess()
    {
        $data = $this->getExemplaryBasicData();
        $this->assertTrue($this->model->validateBasicData($data));
    }

    public function testValidateBasicDataFail()
    {
        $data = $this->getExemplaryBasicData();
        $failCount = 0;
        foreach ($data as $key => $value) {
            $missingData = array_diff_key($data, [$key => $value]);
            if (!$this->model->validateBasicData($missingData)) {
                $failCount++;
            }
        }
        $this->assertEquals($failCount, count($data));
    }

    public function testValidateProductsDataSuccess()
    {
        $data = $this->getExemplaryProductsData();
        $this->assertTrue($this->model->validateProductsData($data));
    }

    public function testValidateProductsDataFailInvalidQuantity()
    {
        $data = $this->getExemplaryProductsData();
        $data['products'][0]['quantity'] = 'string';
        $this->assertFalse($this->model->validateProductsData($data));
    }

    public function testValidateProductsDataFailMissingData()
    {
        $productData = $this->getExemplaryProductData();
        $failCount = 0;
        foreach ($productData as $key => $value) {
            $missingProductData = array_diff_key($productData, [$key => $value]);
            $missingData = [
                'products' => [
                    $missingProductData
                ]
            ];
            if (!$this->model->validateProductsData($missingData)) {
                $failCount++;
            }
        }
        $this->assertEquals($failCount, count($productData));
    }

    public function testValidateStatusUpdateDataSuccess()
    {
        $data = $this->getExemplaryStatusUpdateData();
        $this->assertTrue($this->model->validateStatusUpdateData($data));
    }

    public function testValidateStatusUpdateDataFailMissingKey()
    {
        $data = $this->getExemplaryStatusUpdateData();
        $failCount = 0;
        foreach ($data as $key => $value) {
            $missingData = array_diff_key($data, [$key => $value]);
            if (!$this->model->validateStatusUpdateData($missingData)) {
                $failCount++;
            }
        }
        $this->assertEquals($failCount, count($data));
    }

    public function testValidateStatusUpdateDataOrderStatus()
    {
        $data = $this->getExemplaryStatusUpdateData();
        $validStatuses = $this->getValidStatusUpdateOrderStatuses();
        foreach ($validStatuses as $validStatus) {
            $data['orderStatus'] = $validStatus;
            $this->assertTrue($this->model->validateStatusUpdateData($data));
        }
        $data['orderStatus'] = 'INVALID_STATUS';
        $this->assertFalse($this->model->validateStatusUpdateData($data));
    }

    /**
     * @return array
     */
    protected function getExemplaryBasicData()
    {
        return $this->exemplaryBasicData;
    }

    /**
     * @return array
     */
    protected function getExemplaryProductData()
    {
        return $this->exemplaryProductData;
    }

    /**
     * @return array
     */
    protected function getExemplaryProductsData()
    {
        $data = [
            'products' => [
                $this->getExemplaryProductData()
            ]
        ];
        return $data;
    }

    /**
     * @return array
     */
    protected function getExemplaryStatusUpdateData()
    {
        return $this->exemplaryStatusUpdateData;
    }

    /**
     * @return array
     */
    protected function getValidStatusUpdateOrderStatuses()
    {
        return $this->validStatusUpdateOrderStatuses;
    }
}
