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
    protected $dataValidator;

    /**
     * @var MethodCaller
     */
    protected $methodCaller;

    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var Order\DataGetter
     */
    protected $orderDataGetter;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $logger;

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
    ) {
        $this->dataValidator = $dataValidator;
        $this->methodCaller = $methodCaller;
        $this->transactionResource = $transactionResource;
        $this->orderDataGetter = $orderDataGetter;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function validateCreate($orderId = '', $description = '', $amount = null)
    {
        return
            $this->dataValidator->validateEmpty($orderId) &&
            $this->dataValidator->validateEmpty($description) &&
            $this->dataValidator->validatePositiveInt($amount);
    }

    /**
     * @inheritDoc
     */
    public function create($orderId = '', $description = '', $amount = null)
    {
        $posId = $this->orderDataGetter->getPosId();
        $ts = $this->orderDataGetter->getTs();
        $sig = $this->orderDataGetter->getSigForOrderRetrieve([
            'pos_id' => $posId,
            'session_id' => $orderId,
            'ts' => $ts
        ]);
        $authData = [
            'posId' => $posId,
            'sessionId' => $orderId,
            'ts' => $ts,
            'sig' => $sig
        ];
        $getResult = $this->methodCaller->call('refundGet', [$authData]);
        if ($getResult) {
            $createResult = $this->methodCaller->call('refundAdd', [
                $authData,
                [
                    'refundsHash' => $getResult->refsHash,
                    'amount' => $amount,
                    'desc' => $description,
                    'autoData' => true
                ]
            ]);
            if ($createResult < 0) {
                $this->logger->error('Refund error ' . $createResult . ' for transaction ' . $orderId);
            }
            return $createResult === 0;
        }
        return false;
    }
}
