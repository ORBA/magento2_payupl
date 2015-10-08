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
    protected $_model;

    /**
     * @var array
     */
    protected $_exemplaryBasicData = [
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
        $this->_model = $objectManagerHelper->getObject(
            DataValidator::class,
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

    /**
     * @return array
     */
    protected function _getExemplaryBasicData()
    {
        return $this->_exemplaryBasicData;
    }
}