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
    protected $_dataValidator;

    /**
     * @var MethodCaller
     */
    protected $_methodCaller;

    /**
     * @param Refund\DataValidator $dataValidator
     * @param MethodCaller $methodCaller
     */
    public function __construct(
        Refund\DataValidator $dataValidator,
        MethodCaller $methodCaller
    )
    {
        $this->_dataValidator = $dataValidator;
        $this->_methodCaller = $methodCaller;
    }

    /**
     * @inheritdoc
     */
    public function validateCreate($orderId = '', $description = '', $amount = null)
    {
        return
            $this->_dataValidator->validateEmpty($orderId) &&
            $this->_dataValidator->validateEmpty($description) &&
            (is_null($amount) ? true : $this->_dataValidator->validatePositiveInt($amount));
    }

    /**
     * @inheritdoc
     */
    public function create($orderId = '', $description = '', $amount = null)
    {
        return (bool) ($this->_methodCaller->call('refundCreate', [$orderId, $description, $amount]));
    }
}