<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class Order
{
    /**
     * @var \Orba\Payupl\Model\Resource\Transaction\CollectionFactory
     */
    protected $_transactionCollectionFactory;

    /**
     * @param Resource\Transaction\CollectionFactory $transactionCollectionFactory
     */
    public function __construct(
        \Orba\Payupl\Model\Resource\Transaction\CollectionFactory $transactionCollectionFactory
    )
    {
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getLastPayuplOrderIdByOrderId($orderId)
    {
        /**
         * @var $transactionCollection \Orba\Payupl\Model\Resource\Transaction\Collection
         * @var $transaction \Orba\Payupl\Model\Transaction
         */
        $transactionCollection = $this->_transactionCollectionFactory->create();
        $transactionCollection
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('try', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);
        $transaction = $transactionCollection->getFirstItem();
        if ($transaction->getId()) {
            return $transaction->getPayuplOrderId();
        }
        return false;
    }
}