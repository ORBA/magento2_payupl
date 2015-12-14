<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class Validator
{
    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->transactionResource = $transactionResource;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function validateNoTransactions(\Magento\Sales\Model\Order $order)
    {
        return $this->transactionResource->getLastPayuplOrderIdByOrderId($order->getId()) === false;
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
        return $order->getCustomerId() === $this->customerSession->getCustomerId();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function validateNotPaid(\Magento\Sales\Model\Order $order)
    {
        return !$order->getTotalPaid();
    }
}
