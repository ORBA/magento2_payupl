<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Checkout;

class Fail extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var \Orba\Payupl\Helper\Payment
     */
    protected $_paymentHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Orba\Payupl\Helper\Payment $paymentHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $customerSession,
            $orderFactory,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->_paymentHelper = $paymentHelper;
    }

    public function getRepeatPaymentUrl()
    {
        $orderId = $this->_checkoutSession->getLastOrderId();
        if ($orderId) {
            return $this->_paymentHelper->getRepeatPaymentUrl($orderId);
        }
        return false;
    }
}
