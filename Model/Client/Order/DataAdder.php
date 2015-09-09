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
     * @var \Orba\Payupl\Model\Client\Config
     */
    protected $_configHelper;

    /**
     * @param \Magento\Framework\View\Context $context
     * @param \Orba\Payupl\Model\Client\Config $configHelper
     */
    public function __construct(
        \Magento\Framework\View\Context $context,
        \Orba\Payupl\Model\Client\Config $configHelper
    )
    {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_configHelper = $configHelper;
    }

    /**
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->_urlBuilder->getUrl('orba_payupl/payment/continue');
    }

    /**
     * @return string
     */
    public function getNotifyUrl()
    {
        return $this->_urlBuilder->getUrl('orba_payupl/payment/notify');
    }

    /**
     * @return string
     */
    public function getCustomerIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * @return string
     */
    public function getMerchantPosId()
    {
        return $this->_configHelper->getConfig('merchant_pos_id');
    }
}