<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @var Order\Paytype
     */
    protected $_paytypeHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        Order\Paytype $paytypeHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_paymentHelper = $paymentHelper;
        $this->_paytypeHelper = $paytypeHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        /**
         * @var $payment Payupl
         */
        $config = [];
        $payment = $this->_paymentHelper->getMethodInstance(Payupl::CODE);
        if ($payment->isAvailable()) {
            $redirectUrl = $payment->getCheckoutRedirectUrl();
            $quote = $this->_checkoutSession->getQuote();
            $config = [
                'payment' => [
                    'orbaPayupl' => [
                        'redirectUrl' => $redirectUrl,
                        'paytypes' => $this->_paytypeHelper->getAllForQuote($quote)
                    ]
                ]
            ];
        }
        return $config;
    }
}