<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class Validator
{
    /**
     * @var \Orba\Payupl\Model\Resource\Transaction
     */
    protected $_transactionResource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function __construct(
        \Orba\Payupl\Model\Resource\Transaction $transactionResource,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_transactionResource = $transactionResource;
        $this->_customerSession = $customerSession;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function validateNoTransactions(\Magento\Sales\Model\Order $order)
    {
        return $this->_transactionResource->getLastPayuplOrderIdByOrderId($order->getId()) === false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function validatePaymentMethod(\Magento\Sales\Model\Order $order)
    {
        return $order->getPayment()->getMethod() === \Orba\Payupl\Model\Payupl::CODE;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function validateState(\Magento\Sales\Model\Order $order)
    {
        return !in_array($order->getState(), [
            \Magento\Sales\Model\Order::STATE_CANCELED,
            \Magento\Sales\Model\Order::STATE_CLOSED,
            \Magento\Sales\Model\Order::STATE_COMPLETE
        ]);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function validateCustomer(\Magento\Sales\Model\Order $order)
    {
        return $order->getCustomerId() === $this->_customerSession->getCustomerId();
    }
}