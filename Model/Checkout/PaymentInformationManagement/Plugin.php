<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Checkout\PaymentInformationManagement;

class Plugin
{
    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $session;

    /**
     * @param \Orba\Payupl\Model\Session $session
     */
    public function __construct(
        \Orba\Payupl\Model\Session $session
    ) {
        $this->session = $session;
    }

    /**
     * Copies paytype from payment additional data to session.
     *
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ) {
        if ($paymentMethod->getMethod() === \Orba\Payupl\Model\Payupl::CODE) {
            $additionalData = $paymentMethod->getAdditionalData();
            $this->session->setPaytype(isset($additionalData['paytype']) ? $additionalData['paytype'] : null);
        }
    }
}
