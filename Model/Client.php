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
    protected $_orderHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Client\Config $configHelper
     * @param Client\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Orba\Payupl\Model\Client\Config $configHelper,
        \Orba\Payupl\Model\Client\Order $orderHelper
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_orderHelper = $orderHelper;
        $configHelper->setConfig();
    }

    /**
     * @param array $data
     * @return bool|\OpenPayU_Result
     * @throws Exception
     */
    public function orderCreate(array $data = [])
    {
        if (!$this->_orderHelper->validate($data)) {
            throw new Exception('Order request data array is invalid.');
        }
        $data = $this->_orderHelper->addSpecialData($data);
        $result = $this->_orderHelper->create($data);
        if (!$result) {
            throw new Exception('There was a problem while processing order request.');
        }
        return $result;
    }
}