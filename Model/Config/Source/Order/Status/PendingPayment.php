<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Config\Source\Order\Status;

class PendingPayment extends \Orba\Payupl\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
}
