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
    protected $paymentHelper;

    /**
     * @var Order\Paytype
     */
    protected $paytypeHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        Order\Paytype $paytypeHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->paytypeHelper = $paytypeHelper;
        $this->checkoutSession = $checkoutSession;
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
        $payment = $this->paymentHelper->getMethodInstance(Payupl::CODE);
        if ($payment->isAvailable()) {
            $redirectUrl = $payment->getCheckoutRedirectUrl();
            $quote = $this->checkoutSession->getQuote();
            $config = [
                'payment' => [
                    'orbaPayupl' => [
                        'redirectUrl' => $redirectUrl,
                        'paytypes' => $this->paytypeHelper->getAllForQuote($quote)
                    ]
                ]
            ];
        }
        return $config;
    }
}
