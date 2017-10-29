<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class Payupl extends AbstractMethod
{
    const CODE = 'orba_payupl';

    const XML_PATH_POS_ID               = 'payment/orba_payupl/pos_id';
    const XML_PATH_KEY_MD5              = 'payment/orba_payupl/key_md5';
    const XML_PATH_SECOND_KEY_MD5       = 'payment/orba_payupl/second_key_md5';
    const XML_PATH_POS_AUTH_KEY         = 'payment/orba_payupl/pos_auth_key';
    const XML_PATH_CLASSIC_API          = 'payment/orba_payupl/classic_api';
    const XML_PATH_REST_API_SANDBOX     = 'payment/orba_payupl/sandbox';
    const XML_PATH_PAYTYPES_IN_CHECKOUT = 'payment/orba_payupl/paytypes_in_checkout';

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
    protected $urlBuilder;

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var Order\Paytype
     */
    protected $paytypeHelper;

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
     * @param ResourceModel\Transaction $transactionResource
     * @param Order\Paytype $paytypeHelper
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
        ResourceModel\Transaction $transactionResource,
        Order\Paytype $paytypeHelper,
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
        $this->urlBuilder = $urlBuilder;
        $this->clientFactory = $clientFactory;
        $this->transactionResource = $transactionResource;
        $this->paytypeHelper = $paytypeHelper;
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (is_null($quote)) {
            return parent::isAvailable();
        } else {
            return
                parent::isAvailable($quote) &&
                $this->isShippingMethodAllowed($quote->getShippingAddress()->getShippingMethod()) &&
                $this->paytypeHelper->getAllForQuote($quote) !== [];
        }
    }

    /**
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->urlBuilder->getUrl('orba_payupl/payment/start');
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $payment->getOrder();
        $payuplOrderId = $this->transactionResource->getLastPayuplOrderIdByOrderId($order->getId());
        $client = $this->clientFactory->create();
        $client->refundCreate($payuplOrderId, __('Refund for order # %1', $order->getIncrementId()), $amount * 100);
        return $this;
    }

    /**
     * @param null|string $shippingMethod
     * @return bool
     */
    protected function isShippingMethodAllowed($shippingMethod)
    {
        if ($shippingMethod) {
            $allowedCarriers = explode(',', $this->getConfigData('allowed_carriers'));
            return in_array($shippingMethod, $allowedCarriers);
        }
        return true;
    }
}
