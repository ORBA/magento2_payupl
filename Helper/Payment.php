<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Payment extends AbstractHelper
{
    /**
     * @var \Orba\Payupl\Model\Resource\Transaction
     */
    protected $_transactionResource;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Orba\Payupl\Model\Resource\Transaction $transactionResource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Orba\Payupl\Model\Resource\Transaction $transactionResource,
        \Orba\Payupl\Model\Order $orderHelper
    )
    {
        parent::__construct($context);
        $this->_transactionResource = $transactionResource;
        $this->_orderHelper = $orderHelper;
    }

    /**
     * @param int $orderId
     * @return string|false
     */
    public function getStartPaymentUrl($orderId)
    {
        $order = $this->_orderHelper->loadOrderById($orderId);
        if ($order && $this->_orderHelper->canStartFirstPayment($order)) {
            return $this->_urlBuilder->getUrl('orba_payupl/payment/start', ['id' => $orderId]);
        }
        return false;
    }

    /**
     * @param int $orderId
     * @return string|false
     */
    public function getRepeatPaymentUrl($orderId)
    {
        $order = $this->_orderHelper->loadOrderById($orderId);
        if ($order && $this->_orderHelper->canRepeatPayment($order)) {
            return $this->_urlBuilder->getUrl('orba_payupl/payment/repeat', ['id' => $this->_transactionResource->getLastPayuplOrderIdByOrderId($orderId)]);
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return bool
     */
    public function getOrderIdIfCanRepeat($payuplOrderId = null)
    {
        if ($payuplOrderId && $this->_transactionResource->checkIfNewestByPayuplOrderId($payuplOrderId)) {
            return $this->_transactionResource->getOrderIdByPayuplOrderId($payuplOrderId);
        }
        return false;
    }
}