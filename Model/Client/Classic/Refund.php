<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

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
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $_transactionResource;

    /**
     * @var Order\DataGetter
     */
    protected $_orderDataGetter;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $_logger;

    /**
     * @param Refund\DataValidator $dataValidator
     * @param MethodCaller $methodCaller
     * @param \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource
     * @param Order\DataGetter $orderDataGetter
     */
    public function __construct(
        Refund\DataValidator $dataValidator,
        MethodCaller $methodCaller,
        \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource,
        Order\DataGetter $orderDataGetter,
        \Orba\Payupl\Logger\Logger $logger
    )
    {
        $this->_dataValidator = $dataValidator;
        $this->_methodCaller = $methodCaller;
        $this->_transactionResource = $transactionResource;
        $this->_orderDataGetter = $orderDataGetter;
        $this->_logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function validateCreate($orderId = '', $description = '', $amount = null)
    {
        return
            $this->_dataValidator->validateEmpty($orderId) &&
            $this->_dataValidator->validateEmpty($description) &&
            $this->_dataValidator->validatePositiveInt($amount);
    }

    /**
     * @inheritDoc
     */
    public function create($orderId = '', $description = '', $amount = null)
    {
        $realPayuplOrderId = $this->_transactionResource->getExtOrderIdByPayuplOrderId($orderId);
        if ($realPayuplOrderId) {
            $posId = $this->_orderDataGetter->getPosId();
            $ts = $this->_orderDataGetter->getTs();
            $sig = $this->_orderDataGetter->getSigForOrderRetrieve([
                'pos_id' => $posId,
                'session_id' => $realPayuplOrderId,
                'ts' => $ts
            ]);
            $authData = [
                'posId' => $posId,
                'sessionId' => $realPayuplOrderId,
                'ts' => $ts,
                'sig' => $sig
            ];
            $getResult = $this->_methodCaller->call('refundGet', [$authData]);
            if ($getResult) {
                $createResult = $this->_methodCaller->call('refundAdd', [
                    $authData,
                    [
                        'refundsHash' => $getResult->refsHash,
                        'amount' => $amount,
                        'desc' => $description,
                        'autoData' => true
                    ]
                ]);
                if ($createResult < 0) {
                    $this->_logger->error('Refund error ' . $createResult . ' for transaction ' . $orderId);
                }
                return $createResult === 0;
            }
        }
        return false;
    }
}