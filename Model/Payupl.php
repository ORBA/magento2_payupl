<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class Payupl extends AbstractMethod
{
    const CODE = 'orba_payupl';

    const XML_PATH_POS_ID           = 'payment/orba_payupl/pos_id';
    const XML_PATH_KEY_MD5          = 'payment/orba_payupl/key_md5';
    const XML_PATH_SECOND_KEY_MD5   = 'payment/orba_payupl/second_key_md5';
    const XML_PATH_POS_AUTH_KEY     = 'payment/orba_payupl/pos_auth_key';

    /**
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var string
     */
    protected $_infoBlockType = 'Orba\Payupl\Block\Payment\Info';

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_isOffline = false;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var ClientFactory
     */
    protected $_clientFactory;

    /**
     * @var Resource\Transaction
     */
    protected $_transactionResource;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param ClientFactory $clientFactory
     * @param Resource\Transaction $transactionResource
     * @param array $data
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
        ClientFactory $clientFactory,
        Resource\Transaction $transactionResource,
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
        $this->_clientFactory = $clientFactory;
        $this->_transactionResource = $transactionResource;
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
            return parent::isAvailable($quote) && $this->_isShippingMethodAllowed($quote->getShippingAddress()->getShippingMethod());
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
     * @param null|string $shippingMethod
     * @return bool
     */
    protected function _isShippingMethodAllowed($shippingMethod)
    {
        if ($shippingMethod) {
            $allowedCarriers = explode(',', $this->getConfigData('allowed_carriers'));
            return in_array($shippingMethod, $allowedCarriers);
        }
        return true;
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $payment->getOrder();
        $payuplOrderId = $this->_transactionResource->getLastPayuplOrderIdByOrderId($order->getId());
        $client = $this->_clientFactory->create();
        $client->refundCreate($payuplOrderId, __('Refund for order # %1', $order->getIncrementId()), $amount * 100);
        return $this;
    }
}