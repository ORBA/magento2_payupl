<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Orba\Payupl\Model\Client\Exception;

class Client
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Client\Order
     */
    protected $_orderDataHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Client\Config $configSetter
     * @param Client\Order $orderDataHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Orba\Payupl\Model\Client\Config $configSetter,
        \Orba\Payupl\Model\Client\Order $orderDataHelper
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_orderDataHelper = $orderDataHelper;
        $configSetter->setConfig();
    }

    public function order(array $data = [])
    {
        if (!$this->_orderDataHelper->validate($data)) {
            throw new Exception('Order request data array is invalid.');
        }
        $data = $this->_orderDataHelper->addSpecialData($data);
    }
}