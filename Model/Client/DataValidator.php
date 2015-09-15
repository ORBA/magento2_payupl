<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

class DataValidator
{
    /**
     * @param mixed $data
     * @return bool
     */
    public function validateEmpty($data)
    {
        return !empty($data);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function validatePositiveInt($value)
    {
        return is_integer($value) && $value > 0;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function validatePositiveFloat($value)
    {
        return (is_integer($value) || is_float($value)) && $value > 0;
    }
}