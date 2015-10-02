<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\Order;

use \Orba\Payupl\Model\Client\Rest\Order;
use \Orba\Payupl\Model\Client\Exception;

class Processor
{
    /**
     * @var \Orba\Payupl\Model\Order\Processor
     */
    protected $_orderProcessor;

    public function __construct(
        \Orba\Payupl\Model\Order\Processor $orderProcessor
    )
    {
        $this->_orderProcessor = $orderProcessor;
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @param float $amount
     * @param bool $newest
     * @return bool
     * @throws Exception
     */
    public function processStatusChange($payuplOrderId, $status = '', $amount = null, $newest = true)
    {
        if (!in_array($status, [
            Order::STATUS_NEW,
            Order::STATUS_PENDING,
            Order::STATUS_CANCELLED,
            Order::STATUS_REJECTED,
            Order::STATUS_WAITING,
            Order::STATUS_COMPLETED
        ])) {
            throw new Exception('Invalid status.');
        }
        if (!$newest) {
            $this->_orderProcessor->processOld($payuplOrderId, $status);
            return true;
        }
        switch ($status) {
            case Order::STATUS_NEW:
            case Order::STATUS_PENDING:
                $this->_orderProcessor->processPending($payuplOrderId, $status);
                return true;
            case Order::STATUS_CANCELLED:
            case Order::STATUS_REJECTED:
                $this->_orderProcessor->processHolded($payuplOrderId, $status);
                return true;
            case Order::STATUS_WAITING:
                $this->_orderProcessor->processWaiting($payuplOrderId, $status);
                return true;
            case Order::STATUS_COMPLETED:
                $this->_orderProcessor->processCompleted($payuplOrderId, $status, $amount);
                return true;
        }
    }
}