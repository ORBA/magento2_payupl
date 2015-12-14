<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Orba\Payupl\Model\Client\ConfigInterface;
use Orba\Payupl\Model\Client\Exception as Exception;
use Orba\Payupl\Model\Payupl;

class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function setConfig()
    {
        \OpenPayU_Configuration::setEnvironment('secure');
        $merchantPosId = $this->scopeConfig->getValue(Payupl::XML_PATH_POS_ID, 'store');
        if ($merchantPosId) {
            \OpenPayU_Configuration::setMerchantPosId($merchantPosId);
        } else {
            throw new Exception('Merchant POS ID is empty.');
        }
        $signatureKey = $this->scopeConfig->getValue(Payupl::XML_PATH_SECOND_KEY_MD5, 'store');
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
