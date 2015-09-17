<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

abstract class Order
{
    /**
     * @var \Orba\Payupl\Model\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param \Orba\Payupl\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Orba\Payupl\Model\TransactionFactory $transactionFactory
    )
    {
        $this->_transactionFactory = $transactionFactory;
    }

    /**
     * @inheritdoc
     */
    public function saveNewTransaction($orderId, $payuplOrderId, $payuplExternalOrderId)
    {
        $transaction = $this->_transactionFactory->create();
        $transaction
            ->setOrderId($orderId)
            ->setPayuplOrderId($payuplOrderId)
            ->setPayuplExternalOrderId($payuplExternalOrderId)
            ->setTry(1)
            ->setStatus($this->getNewStatus())
            ->save();
    }

    /**
     * @inheritdoc
     */
    public abstract function getNewStatus();
}