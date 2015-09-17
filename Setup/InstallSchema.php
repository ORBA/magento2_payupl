<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Orba\Payupl\Model\Transaction;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create transaction table
         */
        $transactionTableName = 'orba_payupl_transaction';
        $transactionTable = $installer->getConnection()
            ->newTable($installer->getTable($transactionTableName))
            ->addColumn(
                Transaction::FIELD_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Iransaction ID'
            )->addColumn(
                Transaction::FIELD_ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Order ID'
            )->addColumn(
                Transaction::FIELD_PAYUPL_ORDER_ID,
                Table::TYPE_TEXT,
                32,
                [
                    'nullable' => false
                ],
                'Payu.pl Order ID'
            )->addColumn(
                Transaction::FIELD_PAYUPL_EXTERNAL_ORDER_ID,
                Table::TYPE_TEXT,
                32,
                [
                    'nullable' => false
                ],
                'Payu.pl External Order ID'
            )->addColumn(
                Transaction::FIELD_TRY,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Try #'
            )->addColumn(
                Transaction::FIELD_STATUS,
                Table::TYPE_TEXT,
                32,
                [
                    'nullable' => false
                ],
                'Status'
            )->addColumn(
                Transaction::FIELD_CREATED_AT,
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false
                ],
                'Created At'
            )->addIndex(
                $installer->getIdxName('sales_order', ['order_id']),
                ['order_id']
            )->addForeignKey(
                $installer->getFkName($transactionTableName, 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Payu.pl Transactions Table'
            );
        $installer->getConnection()->createTable($transactionTable);

        $installer->endSetup();
    }
}