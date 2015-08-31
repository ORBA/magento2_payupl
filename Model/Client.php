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
     * @var Client\Order $orderDataHelper
     */
    protected $_orderDataHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Client\Order $orderDataHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Orba\Payupl\Model\Client\Order $orderDataHelper
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_orderDataHelper = $orderDataHelper;
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
        if (!$this->_orderDataHelper->validate($data)) {
            throw new Exception('Order request data array is invalid.');
        }
        $this->setConfig();
        $data = $this->_orderDataHelper->addSpecialData($data);
    }



    protected function _extendOrderData(array $data)
    {
        return array_merge($data, [
            'continueUrl' => $this->_orderDataHelper->getContinueUrl(),
            'notifyUrl' => '',
            'customerIp' => '',
            'merchantPosId' => ''
        ]);
    }
}