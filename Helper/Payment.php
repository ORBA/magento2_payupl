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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Orba\Payupl\Model\Resource\Transaction $transactionResource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Orba\Payupl\Model\Resource\Transaction $transactionResource
    )
    {
        parent::__construct($context);
        $this->_transactionResource = $transactionResource;
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getRepeatPaymentUrl($orderId)
    {
        $payuplOrderId = $this->_transactionResource->getLastPayuplOrderIdByOrderId($orderId);
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
        if ($payuplOrderId && $this->_transactionResource->checkIfNewestByPayuplOrderId($payuplOrderId)) {
            return $this->_transactionResource->getOrderIdByPayuplOrderId($payuplOrderId);
        }
        return false;
    }
}