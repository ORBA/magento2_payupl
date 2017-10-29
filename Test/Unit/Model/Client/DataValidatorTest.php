<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client\DataValidator
     */
    protected $model;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client\DataValidator::class,
            []
        );
    }

    public function testValidateEmpty()
    {
        $this->assertFalse($this->model->validateEmpty(null));
        $this->assertFalse($this->model->validateEmpty(false));
        $this->assertFalse($this->model->validateEmpty(''));
        $this->assertFalse($this->model->validateEmpty([]));
        $this->assertTrue($this->model->validateEmpty(true));
        $this->assertTrue($this->model->validateEmpty('notempty'));
        $this->assertTrue($this->model->validateEmpty(['notempty' => true]));
    }

    public function testValidatePositiveInt()
    {
        $this->assertFalse($this->model->validatePositiveInt('string'));
        $this->assertFalse($this->model->validatePositiveInt(0));
        $this->assertFalse($this->model->validatePositiveInt(-100));
        $this->assertTrue($this->model->validatePositiveInt(100));
        $this->assertFalse($this->model->validatePositiveInt(100.99));
    }

    public function testValidatePositiveFloat()
    {
        $this->assertFalse($this->model->validatePositiveFloat('string'));
        $this->assertFalse($this->model->validatePositiveFloat(0));
        $this->assertFalse($this->model->validatePositiveFloat(-100));
        $this->assertTrue($this->model->validatePositiveFloat(100));
        $this->assertTrue($this->model->validatePositiveFloat(100.99));
    }
}
