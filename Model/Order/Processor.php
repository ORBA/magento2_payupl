<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Processor
{
    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $orderHelper;

    /**
     * @var \Orba\Payupl\Model\Transaction\Service
     */
    protected $transactionService;

    /**
     * @param \Orba\Payupl\Model\Order $orderHelper
     * @param \Orba\Payupl\Model\Transaction\Service $transactionService
     */
    public function __construct(
        \Orba\Payupl\Model\Order $orderHelper,
        \Orba\Payupl\Model\Transaction\Service $transactionService
    ) {
        $this->orderHelper = $orderHelper;
        $this->transactionService = $transactionService;
    }

    /**
     * @param string $payuplOrderId
     * @param string$status
     * @param bool $close
     * @throws LocalizedException
     */
    public function processOld($payuplOrderId, $status, $close = false)
    {
        $this->transactionService->updateStatus($payuplOrderId, $status, $close);
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @throws LocalizedException
     */
    public function processPending($payuplOrderId, $status)
    {
        $this->transactionService->updateStatus($payuplOrderId, $status);
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @throws LocalizedException
     */
    public function processHolded($payuplOrderId, $status)
    {
        $order = $this->loadOrderByPayuplOrderId($payuplOrderId);
        $this->orderHelper->setHoldedOrderStatus($order, $status);
        $this->transactionService->updateStatus($payuplOrderId, $status, true);
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @throws LocalizedException
     * @todo Implement some additional logic for transaction confirmation by store owner.
     */
    public function processWaiting($payuplOrderId, $status)
    {
        $this->transactionService->updateStatus($payuplOrderId, $status);
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @param float $amount
     * @throws LocalizedException
     */
    public function processCompleted($payuplOrderId, $status, $amount)
    {
        $order = $this->loadOrderByPayuplOrderId($payuplOrderId);
        $this->orderHelper->completePayment($order, $amount, $payuplOrderId);
        $this->transactionService->updateStatus($payuplOrderId, $status, true);
    }

    /**
     * @param string $payuplOrderId
     * @return \Orba\Payupl\Model\Sales\Order
     * @throws LocalizedException
     */
    protected function loadOrderByPayuplOrderId($payuplOrderId)
    {
        $order = $this->orderHelper->loadOrderByPayuplOrderId($payuplOrderId);
        if (!$order) {
            throw new LocalizedException(new Phrase('Order not found.'));
        }
        return $order;
    }
}
