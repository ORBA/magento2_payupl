<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use \Orba\Payupl\Model\Client\Classic\Order;

class Processor
{
    /**
     * @var \Orba\Payupl\Model\Order\Processor
     */
    protected $orderProcessor;

    public function __construct(
        \Orba\Payupl\Model\Order\Processor $orderProcessor
    ) {
        $this->orderProcessor = $orderProcessor;
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @param float $amount
     * @param bool $newest
     * @return bool
     * @throws LocalizedException
     */
    public function processStatusChange($payuplOrderId, $status = '', $amount = null, $newest = true)
    {
        if (!in_array($status, [
            Order::STATUS_NEW,
            Order::STATUS_PENDING,
            Order::STATUS_CANCELLED,
            Order::STATUS_REJECTED,
            Order::STATUS_WAITING,
            Order::STATUS_REJECTED_CANCELLED,
            Order::STATUS_COMPLETED,
            Order::STATUS_ERROR
        ])
        ) {
            throw new LocalizedException(new Phrase('Invalid status.'));
        }
        if (!$newest) {
            $close = in_array($status, [
                Order::STATUS_CANCELLED,
                Order::STATUS_REJECTED,
                Order::STATUS_COMPLETED
            ]);
            $this->orderProcessor->processOld($payuplOrderId, $status, $close);
            return true;
        }
        switch ($status) {
            case Order::STATUS_NEW:
            case Order::STATUS_PENDING:
                $this->orderProcessor->processPending($payuplOrderId, $status);
                return true;
            case Order::STATUS_CANCELLED:
            case Order::STATUS_REJECTED:
            case Order::STATUS_REJECTED_CANCELLED:
            case Order::STATUS_ERROR:
                $this->orderProcessor->processHolded($payuplOrderId, $status);
                return true;
            case Order::STATUS_WAITING:
                $this->orderProcessor->processWaiting($payuplOrderId, $status);
                return true;
            case Order::STATUS_COMPLETED:
                $this->orderProcessor->processCompleted($payuplOrderId, $status, $amount);
                return true;
        }
    }
}
