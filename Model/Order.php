<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class Order
{
    /**
     * @var Resource\Transaction
     */
    protected $_transactionResource;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session\SuccessValidator
     */
    protected $_checkoutSuccessValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var Order\Validator
     */
    protected $_orderValidator;

    /**
     * @param ResourceModel\Transaction $transactionResource
     * @param Sales\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session\SuccessValidator $checkoutSuccessValidator
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Order\Validator $orderValidator
     */
    public function __construct(
        ResourceModel\Transaction $transactionResource,
        Sales\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session\SuccessValidator $checkoutSuccessValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        Order\Validator $orderValidator
    )
    {
        $this->_transactionResource = $transactionResource;
        $this->_orderFactory = $orderFactory;
        $this->_checkoutSuccessValidator = $checkoutSuccessValidator;
        $this->_checkoutSession = $checkoutSession;
        $this->_request = $request;
        $this->_orderValidator = $orderValidator;
    }

    /**
     * Saves new order transaction incrementing "try".
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $payuplOrderId
     * @param string $payuplExternalOrderId
     * @param string $status
     */
    public function addNewOrderTransaction(\Magento\Sales\Model\Order $order, $payuplOrderId, $payuplExternalOrderId, $status)
    {
        $orderId = $order->getId();
        $payment = $order->getPayment();
        $payment->setTransactionId($payuplOrderId);
        $payment->setTransactionAdditionalInfo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, [
            'order_id' => $payuplExternalOrderId,
            'try' => $this->_transactionResource->getLastTryByOrderId($orderId) + 1,
            'status' => $status
        ]);
        $payment->setIsTransactionClosed(0);
        $transaction = $payment->addTransaction('order');
        $transaction->save();
        $payment->save();
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
    public function completePayment(Sales\Order $order, $amount, $payuplOrderId)
    {
        $payment = $order->getPayment();
        $payment
            ->setParentTransactionId($payuplOrderId)
            ->setTransactionId($payuplOrderId . ':C')
            ->registerCaptureNotification($amount)
            ->save();
        foreach ($order->getRelatedObjects() as $object) {
            $object->save();
        }
        $order->save();
    }

    /**
     * @return int|false
     */
    public function getOrderIdForPaymentStart()
    {
        if ($this->_checkoutSuccessValidator->isValid()) {
            return $this->_checkoutSession->getLastOrderId();
        }
        $orderId = $this->_request->getParam('id');
        if ($orderId) {
            return $orderId;
        }
        return false;
    }

    /**
     * Checks if first payment can be started.
     *
     * Order should belong to current logged in customer.
     * Order should have Payu.pl payment method.
     * Order should have no Payu.pl transactions.
     * Order shouldn't be cancelled, closed or completed.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canStartFirstPayment(\Magento\Sales\Model\Order $order)
    {
        return
            $this->_orderValidator->validateCustomer($order) &&
            $this->_orderValidator->validateNoTransactions($order) &&
            $this->_orderValidator->validatePaymentMethod($order) &&
            $this->_orderValidator->validateState($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canRepeatPayment(\Magento\Sales\Model\Order $order)
    {
        return
            $this->_orderValidator->validateCustomer($order) &&
            $this->_orderValidator->validatePaymentMethod($order) &&
            $this->_orderValidator->validateState($order) &&
            $this->_orderValidator->validateNotPaid($order) &&
            !$this->_orderValidator->validateNoTransactions($order);
    }

    /**
     * @return bool
     */
    public function paymentSuccessCheck()
    {
        return is_null($this->_request->getParam('exception'));
    }
}