<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Orba\Payupl\Model\Client\RefundInterface;

class Refund implements RefundInterface
{
    /**
     * @var Refund\DataValidator
     */
    protected $dataValidator;

    /**
     * @var MethodCaller
     */
    protected $methodCaller;

    /**
     * @param Refund\DataValidator $dataValidator
     * @param MethodCaller $methodCaller
     */
    public function __construct(
        Refund\DataValidator $dataValidator,
        MethodCaller $methodCaller
    ) {
        $this->dataValidator = $dataValidator;
        $this->methodCaller = $methodCaller;
    }

    /**
     * @inheritdoc
     */
    public function validateCreate($orderId = '', $description = '', $amount = null)
    {
        return
            $this->dataValidator->validateEmpty($orderId) &&
            $this->dataValidator->validateEmpty($description) &&
            (is_null($amount) ? true : $this->dataValidator->validatePositiveInt($amount));
    }

    /**
     * @inheritdoc
     */
    public function create($orderId = '', $description = '', $amount = null)
    {
        return (bool) ($this->methodCaller->call('refundCreate', [$orderId, $description, $amount]));
    }
}
