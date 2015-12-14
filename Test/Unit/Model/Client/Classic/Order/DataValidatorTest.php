<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataValidator
     */
    protected $model;

    /**
     * @var array
     */
    protected $exemplaryBasicData = [
        'amount' => 999,
        'desc' => 'Description',
        'first_name' => 'Jan',
        'last_name' => 'Kowalski',
        'email' => 'jan.kowalski@orba.pl',
        'session_id' => 'ABC',
        'order_id' => '100000001'
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

    /**
     * @return array
     */
    protected function getExemplaryBasicData()
    {
        return $this->exemplaryBasicData;
    }
}
