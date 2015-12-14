<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Payment\Repeat;

class Fail extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Orba\Payupl\Helper\Payment
     */
    protected $paymentHelper;

    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $session;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Orba\Payupl\Model\Session $session,
        \Orba\Payupl\Helper\Payment $paymentHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->session = $session;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @return string|false
     */
    public function getPaymentUrl()
    {
        $orderId = $this->session->getLastOrderId();
        if ($orderId) {
            $repeatPaymentUrl = $this->paymentHelper->getRepeatPaymentUrl($orderId);
            if (!$repeatPaymentUrl) {
                return $this->paymentHelper->getStartPaymentUrl($orderId);
            }
            return $repeatPaymentUrl;
        }
        return false;
    }
}
