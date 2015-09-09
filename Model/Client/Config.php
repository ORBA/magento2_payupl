<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

class Config
{
    const XML_PATH_MERCHANT_POS_ID  = 'payment/orba_payupl/merchant_pos_id';
    const XML_PATH_SIGNATURE_KEY    = 'payment/orba_payupl/signature_key';

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
     * @throws Exception
     */
    public function setConfig()
    {
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
        return true;
    }

    /**
     * @param string $key
     * @return array
     */
    public function getConfig($key = null)
    {
        $config = [
            'merchant_pos_id' => \OpenPayU_Configuration::getMerchantPosId(),
            'signature_key' => \OpenPayU_Configuration::getSignatureKey()
        ];
        if ($key) {
            return $config[$key];
        }
        return $config;
    }

}