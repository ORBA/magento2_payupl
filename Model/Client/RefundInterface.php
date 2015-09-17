<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

interface RefundInterface
{
    /**
     * @param string $orderId
     * @param string $description
     * @param null|int $amount
     * @return bool
     */
    public function validateCreate($orderId = '', $description = '', $amount = null);

    /**
     * Return false on fail or true success.
     *
     * @param string $orderId
     * @param string $description
     * @param null|int $amount
     * @return bool
     */
    public function create($orderId = '', $description = '', $amount = null);
}