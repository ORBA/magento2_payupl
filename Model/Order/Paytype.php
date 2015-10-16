<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class Paytype
{
    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $_clientFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_clientFactory = $clientFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Returns false if paytypes are disabled in checkout or there is no method for paytypes in current API.
     * Returns array of paytypes otherwise.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array|false
     */
    public function getAllForQuote(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        /**
         * @var $client \Orba\Payupl\Model\Client
         */
        if (!$this->_scopeConfig->isSetFlag(\Orba\Payupl\Model\Payupl::XML_PATH_PAYTYPES_IN_CHECKOUT, 'store')) {
            return false;
        }
        $client = $this->_clientFactory->create();
        $paytypes = $client->getPaytypes();
        if ($paytypes === false) {
            return false;
        }
        $total = $quote->getGrandTotal();
        foreach ($paytypes as $key => $paytype) {
            if (!$paytype['enable'] || $total < $paytype['min'] || $total > $paytype['max']) {
                unset ($paytypes[$key]);
            }
        }
        return $paytypes;
    }
}