<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

class Classic extends \Orba\Payupl\Model\Client
{
    /**
     * @param Classic\Config $configHelper
     * @param Classic\Order $orderHelper
     * @param Classic\Refund $refundHelper
     */
    public function __construct(
        Classic\Config $configHelper,
        Classic\Order $orderHelper,
        Classic\Refund $refundHelper
    )
    {
        parent::__construct(
            $configHelper,
            $orderHelper,
            $refundHelper
        );
    }
}