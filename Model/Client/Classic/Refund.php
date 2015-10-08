<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Orba\Payupl\Model\Client\RefundInterface;

class Refund implements RefundInterface
{
    /**
     * @inheritDoc
     */
    public function validateCreate($orderId = '', $description = '', $amount = null)
    {
        // TODO: Implement validateCreate() method.
    }

    /**
     * @inheritDoc
     */
    public function create($orderId = '', $description = '', $amount = null)
    {
        // TODO: Implement create() method.
    }
}