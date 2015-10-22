<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Transaction;

class Service
{
    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    protected $_transactionRepository;

    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $_transactionResource;

    /**
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource
     */
    public function __construct(
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource
    )
    {
        $this->_transactionRepository = $transactionRepository;
        $this->_transactionResource = $transactionResource;
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @param bool $close
     * @throws Exception
     */
    public function updateStatus($payuplOrderId, $status, $close = false)
    {
        /**
         * @var $transaction \Magento\Sales\Model\Order\Payment\Transaction
         */
        $id = $this->_transactionResource->getIdByPayuplOrderId($payuplOrderId);
        if (!$id) {
            throw new Exception('Transaction ' . $payuplOrderId . ' not found.');
        }
        $transaction = $this->_transactionRepository->get($id);
        if ($close) {
            $transaction->setIsClosed(1);
        }
        $rawDetailsInfo = $transaction->getAdditionalInformation(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS);
        $rawDetailsInfo['status'] = $status;
        $transaction
            ->setAdditionalInformation(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $rawDetailsInfo)
            ->save();
    }
}