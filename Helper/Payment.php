<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Payment extends AbstractHelper
{
    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $orderHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource,
        \Orba\Payupl\Model\Order $orderHelper
    ) {
        parent::__construct($context);
        $this->transactionResource = $transactionResource;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @param int $orderId
     * @return string|false
     */
    public function getStartPaymentUrl($orderId)
    {
        $order = $this->orderHelper->loadOrderById($orderId);
        if ($order && $this->orderHelper->canStartFirstPayment($order)) {
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
        $order = $this->orderHelper->loadOrderById($orderId);
        if ($order && $this->orderHelper->canRepeatPayment($order)) {
            return $this->_urlBuilder->getUrl(
                'orba_payupl/payment/repeat',
                ['id' => base64_encode($this->transactionResource->getLastPayuplOrderIdByOrderId($orderId))]
            );
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return bool
     */
    public function getOrderIdIfCanRepeat($payuplOrderId = null)
    {
        if ($payuplOrderId && $this->transactionResource->checkIfNewestByPayuplOrderId($payuplOrderId)) {
            return $this->transactionResource->getOrderIdByPayuplOrderId($payuplOrderId);
        }
        return false;
    }
}
