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

    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper
    )
    {
        $this->_paymentHelper = $paymentHelper;
    }

    public function getConfig()
    {
        /**
         * @var $payment Payupl
         */
        $config = [];
        $payment = $this->_paymentHelper->getMethodInstance(Payupl::CODE);
        if ($payment->isAvailable()) {
            $redirectUrl = $payment->getCheckoutRedirectUrl();
            $config = [
                'payment' => [
                    'orbaPayupl' => [
                        'redirectUrl' => $redirectUrl
                    ]
                ]
            ];
        }
        return $config;
    }
}