<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class Order
{
    /**
     * @var Resource\Transaction\CollectionFactory
     */
    protected $_transactionCollectionFactory;

    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var Resource\Transaction
     */
    protected $_transactionResource;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param Resource\Transaction\CollectionFactory $transactionCollectionFactory
     * @param TransactionFactory $transactionFactory
     * @param Resource\Transaction $transactionResource
     * @param Sales\OrderFactory $orderFactory
     */
    public function __construct(
        Resource\Transaction\CollectionFactory $transactionCollectionFactory,
        TransactionFactory $transactionFactory,
        Resource\Transaction $transactionResource,
        Sales\OrderFactory $orderFactory
    )
    {
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_transactionResource = $transactionResource;
        $this->_orderFactory = $orderFactory;
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
         * @var $transactionCollection Resource\Transaction\Collection
         * @var $transaction Transaction
         * @var $transactionToSave Transaction
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

    /**
     * @param int $orderId
     * @return Sales\Order|false
     */
    public function loadOrderById($orderId)
    {
        /**
         * @var $order Sales\Order
         */
        $order = $this->_orderFactory->create();
        $order->load($orderId);
        if ($order->getId()) {
            return $order;
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return Sales\Order|false
     */
    public function loadOrderByPayuplOrderId($payuplOrderId)
    {
        $orderId = $this->_transactionResource->getOrderIdByPayuplOrderId($payuplOrderId);
        if ($orderId) {
            return $this->loadOrderById($orderId);
        }
        return false;
    }

    /**
     * @param Sales\Order $order
     */
    public function setNewOrderStatus(Sales\Order $order)
    {
        $order
            ->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
            ->addStatusToHistory(true)
            ->save();
    }

    /**
     * @param Sales\Order $order
     * @param string $status
     */
    public function setHoldedOrderStatus(Sales\Order $order, $status)
    {
        $orderState = Sales\Order::STATE_HOLDED;
        $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
        $order
            ->setHoldBeforeState($order->getState())
            ->setHoldBeforeStatus($order->getStatus())
            ->setState($orderState)
            ->setStatus($orderStatus);
        $order->addStatusHistoryComment(__('Payu.pl status') . ': ' . $status);
        $order->save();
    }

    /**
     * Registers payment, creates invoice and changes order statatus.
     *
     * @param Sales\Order $order
     * @param float $amount
     */
    public function completePayment(Sales\Order $order, $amount)
    {
        $payment = $order->getPayment();
        $payment
            ->registerCaptureNotification($amount)
            ->save();
        foreach ($order->getRelatedObjects() as $object) {
            $object->save();
        }
        $order->save();
    }
}