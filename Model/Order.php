<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class Order
{
    /**
     * @var \Orba\Payupl\Model\Resource\Transaction\CollectionFactory
     */
    protected $_transactionCollectionFactory;

    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param Resource\Transaction\CollectionFactory $transactionCollectionFactory
     */
    public function __construct(
        \Orba\Payupl\Model\Resource\Transaction\CollectionFactory $transactionCollectionFactory,
        \Orba\Payupl\Model\TransactionFactory $transactionFactory
    )
    {
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
        $this->_transactionFactory = $transactionFactory;
    }

    /**
     * @param $orderId
     * @return bool|string
     */
    public function getLastPayuplOrderIdByOrderId($orderId)
    {
        /**
         * @var $transactionCollection \Orba\Payupl\Model\Resource\Transaction\Collection
         * @var $transaction \Orba\Payupl\Model\Transaction
         */
        $transactionCollection = $this->_transactionCollectionFactory->create();
        $transactionCollection
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('try', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        $transaction = $transactionCollection->getFirstItem();
        if ($transaction->getId()) {
            return $transaction->getPayuplOrderId();
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return bool
     */
    public function checkIfNewestByPayuplOrderId($payuplOrderId)
    {
        /**
         * @var $transactionCollection \Orba\Payupl\Model\Resource\Transaction\Collection
         * @var $transaction \Orba\Payupl\Model\Transaction
         */
        $transactionCollection = $this->_transactionCollectionFactory->create();
        $transactionCollection
            ->addFieldToFilter('main_table.payupl_order_id', $payuplOrderId);
        $select = $transactionCollection->getSelect();
        $select->joinLeft(
            ['t2' => 'orba_payupl_transaction'],
            't2.order_id = main_table.order_id AND t2.try > main_table.try',
            ['newer_id' => 't2.order_id']
        );
        $transaction = $transactionCollection->getFirstItem();
        if ($transaction->getId() && !$transaction->getNewerId()) {
            return true;
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return bool|int
     */
    public function getOrderIdByPayuplOrderId($payuplOrderId)
    {
        /**
         * @var $transactionCollection \Orba\Payupl\Model\Resource\Transaction\Collection
         * @var $transaction \Orba\Payupl\Model\Transaction
         */
        $transactionCollection = $this->_transactionCollectionFactory->create();
        $transactionCollection
            ->addFieldToFilter('payupl_order_id', $payuplOrderId);
        $transaction = $transactionCollection->getFirstItem();
        if ($transaction->getId()) {
            return $transaction->getOrderId();
        }
        return false;
    }

    /**
     * Saves new transaction incrementing "try".
     *
     * @param int $orderId
     * @param string $payuplOrderId
     * @param string $payuplExternalOrderId
     * @param string $status
     */
    public function saveNewTransaction($orderId, $payuplOrderId, $payuplExternalOrderId, $status)
    {
        /**
         * @var $transactionCollection \Orba\Payupl\Model\Resource\Transaction\Collection
         * @var $transaction \Orba\Payupl\Model\Transaction
         * @var $transactionToSave \Orba\Payupl\Model\Transaction
         */
        $transactionCollection = $this->_transactionCollectionFactory->create();
        $transactionCollection
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('try', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        $transaction = $transactionCollection->getFirstItem();
        $transactionToSave = $this->_transactionFactory->create();
        $transactionToSave
            ->setOrderId($orderId)
            ->setPayuplOrderId($payuplOrderId)
            ->setPayuplExternalOrderId($payuplExternalOrderId)
            ->setTry($transaction->getTry() + 1)
            ->setStatus($status)
            ->save();
    }
}