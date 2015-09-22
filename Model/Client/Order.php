<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

abstract class Order
{
    const XML_PATH_ORDER_STATUS_NEW = 'payment/orba_payupl/order_status_new';

    /**
     * @var \Orba\Payupl\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Orba\Payupl\Model\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Orba\Payupl\Model\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_transactionFactory = $transactionFactory;
        $this->_orderFactory = $orderFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function loadOrderById($orderId)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $this->_orderFactory->create();
        $order->load($orderId);
        if ($order->getId()) {
            return $order;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function saveNewTransaction($orderId, $payuplOrderId, $payuplExternalOrderId)
    {
        $transaction = $this->_transactionFactory->create();
        $transaction
            ->setOrderId($orderId)
            ->setPayuplOrderId($payuplOrderId)
            ->setPayuplExternalOrderId($payuplExternalOrderId)
            ->setTry(1)
            ->setStatus($this->getNewStatus())
            ->save();
    }

    /**
     * @inheritdoc
     */
    public abstract function getNewStatus();

    /**
     * @inheritdoc
     */
    public function setNewOrderStatus(\Magento\Sales\Model\Order $order)
    {
        $orderStatus = $this->_scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_NEW);
        $order
            ->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
            ->addStatusToHistory($orderStatus)
            ->save();
    }
}