<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Resource;

use Magento\Framework\Model\Resource\Db\AbstractDb;
use Orba\Payupl\Model\Transaction as Model;

class Transaction extends AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $date,
        $resourcePrefix = null
    )
    {
        parent::__construct(
            $context,
            $resourcePrefix
        );
        $this->_date = $date;
    }

    /**
     * @param int $orderId
     * @return string|false
     */
    public function getLastPayuplOrderIdByOrderId($orderId)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from(
                ['main_table' => $this->_resources->getTableName('orba_payupl_transaction')],
                ['payupl_order_id']
            )->where('order_id = ?', $orderId)
            ->order('try ' . \Zend_Db_Select::SQL_DESC)
            ->limit(1);
        $row = $adapter->fetchRow($select);
        if ($row) {
            return $row['payupl_order_id'];
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return bool
     */
    public function checkIfNewestByPayuplOrderId($payuplOrderId)
    {
        $transactionTableName = $this->_resources->getTableName('orba_payupl_transaction');
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from(
                ['main_table' => $transactionTableName],
                ['transaction_id']
            )->joinLeft(
                ['t2' => $transactionTableName],
                't2.order_id = main_table.order_id AND t2.try > main_table.try',
                ['newer_id' => 't2.order_id']
            )->where('main_table.payupl_order_id = ?', $payuplOrderId)
            ->limit(1);
        $row = $adapter->fetchRow($select);
        if ($row && is_null($row['newer_id'])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $payuplOrderId
     * @return int|false
     */
    public function getOrderIdByPayuplOrderId($payuplOrderId)
    {
        return $this->_getOneFieldByAnother('order_id', 'payupl_order_id', $payuplOrderId);
    }

    public function getStatusByPayuplOrderId($payuplOrderId)
    {
        return $this->_getOneFieldByAnother('status', 'payupl_order_id', $payuplOrderId);
    }

    protected function _construct()
    {
        $this->_init('orba_payupl_transaction', Model::FIELD_ID);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * @var $object Model
         */
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->_date->formatDate(true));
        }
        return $this;
    }

    /**
     * @param string $getFieldName
     * @param string $byFieldName
     * @param mixed $value
     * @return mixed|false
     */
    protected function _getOneFieldByAnother($getFieldName, $byFieldName, $value)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from(
                ['main_table' => $this->_resources->getTableName('orba_payupl_transaction')],
                [$getFieldName]
            )->where($byFieldName . ' = ?', $value)
            ->limit(1);
        $row = $adapter->fetchRow($select);
        if ($row) {
            return $row[$getFieldName];
        }
        return false;
    }
}