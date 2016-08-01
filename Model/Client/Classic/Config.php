<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Orba\Payupl\Model\Client\ConfigInterface;
use Orba\Payupl\Model\Payupl;

class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $posId;

    /**
     * @var string
     */
    protected $keyMd5;

    /**
     * @var string
     */
    protected $secondKeyMd5;

    /**
     * @var string
     */
    protected $posAuthKey;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return true
     * @throws LocalizedException
     */
    public function setConfig()
    {
        $posId = $this->scopeConfig->getValue(Payupl::XML_PATH_POS_ID, 'store');
        if ($posId) {
            $this->posId = $posId;
        } else {
            throw new LocalizedException(new Phrase('POS ID is empty.'));
        }
        $keyMd5 = $this->scopeConfig->getValue(Payupl::XML_PATH_KEY_MD5, 'store');
        if ($keyMd5) {
            $this->keyMd5 = $keyMd5;
        } else {
            throw new LocalizedException(new Phrase('Key MD5 is empty.'));
        }
        $secondKeyMd5 = $this->scopeConfig->getValue(Payupl::XML_PATH_SECOND_KEY_MD5, 'store');
        if ($secondKeyMd5) {
            $this->secondKeyMd5 = $secondKeyMd5;
        } else {
            throw new LocalizedException(new Phrase('Second key MD5 is empty.'));
        }
        $posAuthKey = $this->scopeConfig->getValue(Payupl::XML_PATH_POS_AUTH_KEY, 'store');
        if ($posAuthKey) {
            $this->posAuthKey = $posAuthKey;
        } else {
            throw new LocalizedException(new Phrase('POS auth key is empty.'));
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
            'pos_id' => $this->posId,
            'key_md5' => $this->keyMd5,
            'second_key_md5' => $this->secondKeyMd5,
            'pos_auth_key' => $this->posAuthKey
        ];
        if ($key) {
            return $config[$key];
        }
        return $config;
    }
}
