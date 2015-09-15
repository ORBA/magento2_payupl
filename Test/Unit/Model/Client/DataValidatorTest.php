<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client\DataValidator
     */
    protected $_model;

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

    public function testValidatePositiveInt()
    {
        $this->assertFalse($this->_model->validatePositiveInt('string'));
        $this->assertFalse($this->_model->validatePositiveInt(0));
        $this->assertFalse($this->_model->validatePositiveInt(-100));
        $this->assertTrue($this->_model->validatePositiveInt(100));
        $this->assertFalse($this->_model->validatePositiveInt(100.99));
    }

    public function testValidatePositiveFloat()
    {
        $this->assertFalse($this->_model->validatePositiveFloat('string'));
        $this->assertFalse($this->_model->validatePositiveFloat(0));
        $this->assertFalse($this->_model->validatePositiveFloat(-100));
        $this->assertTrue($this->_model->validatePositiveFloat(100));
        $this->assertTrue($this->_model->validatePositiveFloat(100.99));
    }
}