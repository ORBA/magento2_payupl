<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

class Refund
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
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $_logger;

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
     * @param string $orderId
     * @param string $description
     * @param null|int $amount
     * @return bool
     */
    public function validateCreate($orderId = '', $description = '', $amount = null)
    {
        return
            $this->_dataValidator->validateEmpty($orderId) &&
            $this->_dataValidator->validateEmpty($description) &&
            (is_null($amount) ? true : $this->_dataValidator->validatePositiveInt($amount));
    }

    /**
     * @param string $orderId
     * @param string $description
     * @param null|int $amount
     * @return bool|\OpenPayU_Result
     */
    public function create($orderId = '', $description = '', $amount = null)
    {
        return $this->_methodCaller->call('refundCreate', [$orderId, $description, $amount]);
    }
}