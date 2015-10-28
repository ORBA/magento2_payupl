<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $dbVersion = $context->getVersion();
        $connection = $setup->getConnection();

        if (version_compare($dbVersion, '0.1.0', '<')) {
            // add Pending Payu.pl status to Pending Payment state
            $connection->insert($setup->getTable('sales_order_status'), [
                'status' => 'pending_payupl',
                'label' => 'Pending Payu.pl'
            ]);
            $connection->insert($setup->getTable('sales_order_status_state'), [
                'status' =>  'pending_payupl',
                'state' => 'pending_payment',
                'is_default' => 0,
                'visible_on_front' => 1
            ]);
        }
    }
}