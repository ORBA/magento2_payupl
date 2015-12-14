<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\Order;

class DataValidator extends \Orba\Payupl\Model\Client\DataValidator
{
    /**
     * @var array
     */
    protected $requiredBasicKeys = [
        'amount',
        'desc',
        'first_name',
        'last_name',
        'email',
        'session_id',
        'order_id'
    ];

    /**
     * @param array $data
     * @return bool
     */
    public function validateBasicData(array $data = [])
    {
        foreach ($this->getRequiredBasicKeys() as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    protected function getRequiredBasicKeys()
    {
        return $this->requiredBasicKeys;
    }
}
