<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

class Rest extends \Orba\Payupl\Model\Client
{
    /**
     * @inheritdoc
     */
    protected $clientType = self::TYPE_REST;

    /**
     * @param Rest\Config $configHelper
     * @param Rest\Order $orderHelper
     * @param Rest\Refund $refundHelper
     */
    public function __construct(
        Rest\Config $configHelper,
        Rest\Order $orderHelper,
        Rest\Refund $refundHelper
    ) {
        parent::__construct(
            $configHelper,
            $orderHelper,
            $refundHelper
        );
    }
}
