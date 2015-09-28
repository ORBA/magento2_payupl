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
     * @param int $orderId
     * @param string $status
     * @param bool $newest
     * @return bool
     * @throws Exception
     */
    public function processStatusChange($orderId, $status = '', $newest = true)
    {
        switch ($status) {
            case Order::STATUS_NEW:
            case Order::STATUS_PENDING:
                return true;
            case Order::STATUS_CANCELLED:
            case Order::STATUS_REJECTED:
                $this->_orderProcessor->processHold($orderId, $newest);
                return true;
            case Order::STATUS_WAITING:
                $this->_orderProcessor->processWaiting($orderId, $newest);
                return true;
            case Order::STATUS_COMPLETED:
                $this->_orderProcessor->processCompleted($orderId, $newest);
                return true;
            default:
                throw new Exception('Invalid status.');
        }
    }
}