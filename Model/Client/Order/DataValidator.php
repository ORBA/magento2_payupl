<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Order;

class DataValidator
{
    protected $_requiredProductKeys = [
        'name',
        'unitPrice',
        'quantity'
    ];

    /**
     * @var array
     */
    protected $_requiredBasicKeys = [
        'description',
        'currencyCode',
        'totalAmount',
        'extOrderId',
        'products'
    ];

    /**
     * @param mixed $data
     * @return bool
     */
    public function validateEmpty($data)
    {
        return !empty($data);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateBasicData(array $data = [])
    {
        foreach ($this->_getRequiredBasicKeys() as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateProductsData(array $data = [])
    {
        if (isset($data['products']) && !empty($data['products'])) {
            $requiredProductKeys = $this->_getRequiredProductKeys();
            foreach ($data['products'] as $productData) {
                foreach ($requiredProductKeys as $key) {
                    if (!isset($productData[$key]) || empty($productData[$key])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @return array
     */
    protected function _getRequiredBasicKeys()
    {
        return $this->_requiredBasicKeys;
    }

    /**
     * @return array
     */
    protected function _getRequiredProductKeys()
    {
        return $this->_requiredProductKeys;
    }
}