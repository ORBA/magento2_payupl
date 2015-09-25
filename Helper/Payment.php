<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Payment extends AbstractHelper
{
    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Orba\Payupl\Model\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Orba\Payupl\Model\Order $orderHelper
    )
    {
        parent::__construct($context);
        $this->_orderHelper = $orderHelper;
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getRepeatPaymentUrl($orderId)
    {
        $payuplOrderId = $this->_orderHelper->getLastPayuplOrderIdByOrderId($orderId);
        if ($payuplOrderId) {
            return $this->_urlBuilder->getUrl('orba_payupl/payment/repeat', ['id' => $payuplOrderId]);
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return bool
     */
    public function getOrderIdIfCanRepeat($payuplOrderId = null)
    {
        if ($payuplOrderId && $this->_orderHelper->checkIfNewestByPayuplOrderId($payuplOrderId)) {
            return $this->_orderHelper->getOrderIdByPayuplOrderId($payuplOrderId);
        }
        return false;
    }
}