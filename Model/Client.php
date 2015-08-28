<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Orba\Payupl\Model\Client\Exception;

class Client
{
    const XML_PATH_MERCHANT_POS_ID  = 'payment/orba_payupl/merchant_pos_id';
    const XML_PATH_SIGNATURE_KEY    = 'payment/orba_payupl/signature_key';

    /**
     * @var bool
     */
    protected $_isConfigSet = false;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isConfigSet()
    {
        return $this->_isConfigSet;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function setConfig()
    {
        if (!$this->isConfigSet()) {
            $merchantPosId = $this->_scopeConfig->getValue(self::XML_PATH_MERCHANT_POS_ID);
            if ($merchantPosId) {
                \OpenPayU_Configuration::setMerchantPosId($merchantPosId);
            } else {
                throw new Exception('Merchant POS ID is empty.');
            }
            $signatureKey = $this->_scopeConfig->getValue(self::XML_PATH_SIGNATURE_KEY);
            if ($signatureKey) {
                \OpenPayU_Configuration::setSignatureKey($signatureKey);
            } else {
                throw new Exception('Signature key is empty.');
            }
            $this->_isConfigSet = true;
        }
        return true;
    }

    /**
     * @return array
     */
    public function getCurrentConfigFromSDK()
    {
        return [
            'merchant_pos_id' => \OpenPayU_Configuration::getMerchantPosId(),
            'signature_key' => \OpenPayU_Configuration::getSignatureKey()
        ];
    }

    public function order(array $data = [])
    {
        if (!isset($data) || empty($data)) {
            throw new Exception('Order request data array is empty.');
        }
        foreach ($this->_getRequiredOrderDataArrayKeys() as $key) {
            if (!isset($data[$key]) || empty($data[$key])) {
                throw new Exception('Order request data array basic element "' . $key . '" is missing.');
            }
        }
        $this->setConfig();
    }

    protected function _getRequiredOrderDataArrayKeys()
    {
        return [
            'continueUrl',
            'notifyUrl',
            'description',
            'currencyCode',
            'totalAmount',
            'extOrderId'
        ];
    }
}