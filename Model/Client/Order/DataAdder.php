<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Order;

class DataAdder
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Framework\View\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Context $context
    )
    {
        $this->_urlBuilder = $context->getUrlBuilder();
    }

    public function getContinueUrl()
    {
        return $this->_urlBuilder->getUrl('orba_payupl/payment/continue');
    }

    public function getNotifyUrl()
    {
        return $this->_urlBuilder->getUrl('orba_payupl/payment/notify');
    }

    public function getCustomerIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function getMerchantPosId()
    {
    }
}