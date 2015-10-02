<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Transaction;

use Orba\Payupl\Model\Transaction;

class Service
{
    /**
     * @var \Orba\Payupl\Model\TransactionFactory
     */
    protected $_transactionFactory;

    public function __construct(
        \Orba\Payupl\Model\TransactionFactory $transactionFactory
    )
    {
        $this->_transactionFactory = $transactionFactory;
    }

    public function updateStatus($payuplOrderId, $status)
    {
        /**
         * @var $transaction Transaction
         */
        $transaction = $this->_transactionFactory->create();
        $transaction->load($payuplOrderId, 'payupl_order_id');
        if (!$transaction->getId()) {
            throw new Exception('Transaction not found.');
        }
        $transaction
            ->setStatus($status)
            ->save();
    }
}