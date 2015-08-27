<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class Payupl extends AbstractMethod
{
    protected $_code = 'orba_payupl';

    protected $_isOffline = true;

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (is_null($quote)) {
            return false;
        }
        return parent::isAvailable($quote) && $this->_isCarrierAllowed($quote->getShippingAddress()->getShippingMethod());
    }

    /**
     * @param string $shippingMethod
     * @return bool
     */
    protected function _isCarrierAllowed($shippingMethod)
    {
        $allowedCarriers = explode(',', $this->getConfigData('allowed_carriers'));
        return in_array($shippingMethod, $allowedCarriers);
    }
}