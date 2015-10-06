<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Transaction;

class Service
{
    /**
     * @var \Magento\Sales\Model\Order\Payment\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    public function __construct(
        \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory,
        \Orba\Payupl\Model\Order $orderHelper
    )
    {
        $this->_transactionFactory = $transactionFactory;
        $this->_orderHelper = $orderHelper;
    }

    public function updateStatus($payuplOrderId, $status, $close = false)
    {
        /**
         * @var $transaction \Magento\Sales\Model\Order\Payment\Transaction
         */
        $order = $this->_orderHelper->loadOrderByPayuplOrderId($payuplOrderId);
        if (!$order) {
            throw new Exception('Transaction not found.');
        }
        $transaction = $order->getPayment()->getTransaction($payuplOrderId);
        if ($close) {
            $transaction->setIsClosed(1);
        }
        $rawDetailsInfo = $transaction->getAdditionalInformation(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS);
        $rawDetailsInfo['status'] = $status;
        $transaction
            ->setAdditionalInformation(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $rawDetailsInfo)
            ->save();
    }
}