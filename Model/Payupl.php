<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class Payupl extends AbstractMethod
{
    const CODE = 'orba_payupl';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_isOffline = false;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (is_null($quote)) {
            return parent::isAvailable();
        } else {
            return parent::isAvailable($quote) && $this->_isCarrierAllowed($quote->getShippingAddress()->getShippingMethod());
        }
    }

    /**
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('orba_payupl/payment/start');
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