<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Orba\Payupl\Model\Client\ConfigInterface;
use Orba\Payupl\Model\Client\Exception as Exception;
use Orba\Payupl\Model\Payupl;

class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var string
     */
    protected $_posId;

    /**
     * @var string
     */
    protected $_keyMd5;

    /**
     * @var string
     */
    protected $_secondKeyMd5;

    /**
     * @var string
     */
    protected $_posAuthKey;

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
     * @return true
     * @throws Exception
     */
    public function setConfig() {
        $posId = $this->_scopeConfig->getValue(Payupl::XML_PATH_POS_ID);
        if ($posId) {
            $this->_posId = $posId;
        } else {
            throw new Exception('POS ID is empty.');
        }
        $keyMd5 = $this->_scopeConfig->getValue(Payupl::XML_PATH_KEY_MD5);
        if ($keyMd5) {
            $this->_keyMd5 = $keyMd5;
        } else {
            throw new Exception('Key MD5 is empty.');
        }
        $secondKeyMd5 = $this->_scopeConfig->getValue(Payupl::XML_PATH_SECOND_KEY_MD5);
        if ($secondKeyMd5) {
            $this->_secondKeyMd5 = $secondKeyMd5;
        } else {
            throw new Exception('Second key MD5 is empty.');
        }
        $posAuthKey = $this->_scopeConfig->getValue(Payupl::XML_PATH_POS_AUTH_KEY);
        if ($posAuthKey) {
            $this->_posAuthKey = $posAuthKey;
        } else {
            throw new Exception('POS auth key is empty.');
        }
        return true;
    }

    /**
     * @param string|null $key
     * @return string|array
     */
    public function getConfig($key = null)
    {
        $config = [
            'pos_id' => $this->_posId,
            'key_md5' => $this->_keyMd5,
            'second_key_md5' => $this->_secondKeyMd5,
            'pos_auth_key' => $this->_posAuthKey
        ];
        if ($key) {
            return $config[$key];
        }
        return $config;
    }
}