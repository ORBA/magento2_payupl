<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class Processor
{
    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    /**
     * @var \Orba\Payupl\Model\Transaction\Service
     */
    protected $_transactionService;

    /**
     * @param \Orba\Payupl\Model\Order $orderHelper
     * @param \Orba\Payupl\Model\Transaction\Service $transactionService
     */
    public function __construct(
        \Orba\Payupl\Model\Order $orderHelper,
        \Orba\Payupl\Model\Transaction\Service $transactionService
    )
    {
        $this->_orderHelper = $orderHelper;
        $this->_transactionService = $transactionService;
    }

    /**
     * @param string $payuplOrderId
     * @param string$status
     * @param bool $close
     * @throws \Orba\Payupl\Model\Transaction\Exception
     */
    public function processOld($payuplOrderId, $status, $close = false)
    {
        $this->_transactionService->updateStatus($payuplOrderId, $status, $close);
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @throws \Orba\Payupl\Model\Transaction\Exception
     */
    public function processPending($payuplOrderId, $status)
    {
        $this->_transactionService->updateStatus($payuplOrderId, $status);
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @throws Exception
     * @throws \Orba\Payupl\Model\Transaction\Exception
     */
    public function processHolded($payuplOrderId, $status)
    {
        $order = $this->_loadOrderByPayuplOrderId($payuplOrderId);
        $this->_orderHelper->setHoldedOrderStatus($order, $status);
        $this->_transactionService->updateStatus($payuplOrderId, $status, true);
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @throws \Orba\Payupl\Model\Transaction\Exception
     * @todo Implement some additional logic for transaction confirmation by store owner.
     */
    public function processWaiting($payuplOrderId, $status)
    {
        $this->_transactionService->updateStatus($payuplOrderId, $status);
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @param float $amount
     * @throws Exception
     * @throws \Orba\Payupl\Model\Transaction\Exception
     */
    public function processCompleted($payuplOrderId, $status, $amount)
    {
        $order = $this->_loadOrderByPayuplOrderId($payuplOrderId);
        $this->_orderHelper->completePayment($order, $amount, $payuplOrderId);
        $this->_transactionService->updateStatus($payuplOrderId, $status, true);
    }

    /**
     * @param string $payuplOrderId
     * @return \Magento\Sales\Model\Order
     * @throws Exception
     */
    protected function _loadOrderByPayuplOrderId($payuplOrderId)
    {
        $order = $this->_orderHelper->loadOrderByPayuplOrderId($payuplOrderId);
        if (!$order) {
            throw new Exception('Order not found.');
        }
        return $order;
    }
}