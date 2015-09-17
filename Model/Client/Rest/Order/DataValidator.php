<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\Order;

class DataValidator extends \Orba\Payupl\Model\Client\DataValidator
{
    /**
     * @var array
     */
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
     * @var array
     */
    protected $_requiredStatusUpdateKeys = [
        'orderId',
        'orderStatus'
    ];

    /**
     * @var array
     */
    protected $validStatusUpdateOrderStatuses = [
        'COMPLETED',
        'REJECTED'
    ];

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
                    if (!isset($productData[$key]) || $productData[$key] === '') {
                        return false;
                    }
                    if ($key === 'quantity' && !$this->validatePositiveFloat($productData[$key])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateStatusUpdateData($data)
    {
        foreach ($this->_getRequiredStatusUpdateKeys() as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                return false;
            }
        }
        $validStatuses = $this->_getValidStatusUpdateOrderStatuses();
        if (!in_array($data['orderStatus'], $validStatuses)) {
            return false;
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

    /**
     * @return array
     */
    protected function _getRequiredStatusUpdateKeys()
    {
        return $this->_requiredStatusUpdateKeys;
    }

    /**
     * @return array
     */
    protected function _getValidStatusUpdateOrderStatuses()
    {
        return $this->validStatusUpdateOrderStatuses;
    }

}