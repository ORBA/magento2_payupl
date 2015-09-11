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
     * @var Sdk
     */
    protected $_sdk;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $_logger;

    public function __construct(
        Refund\DataValidator $dataValidator,
        Sdk $sdk,
        \Orba\Payupl\Logger\Logger $logger
    )
    {
        $this->_dataValidator = $dataValidator;
        $this->_sdk = $sdk;
        $this->_logger = $logger;
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
        try {
            return $this->_sdk->refundCreate($orderId, $description, $amount);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return false;
        }
    }
}