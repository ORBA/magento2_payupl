<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Sales\Model\Order\StatusFactory
     */
    protected $statusFactory;

    /**
     * @param \Magento\Sales\Model\Order\StatusFactory $statusFactory
     */
    public function __construct(\Magento\Sales\Model\Order\StatusFactory $statusFactory)
    {
        $this->statusFactory = $statusFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // add Pending Payu.pl status to Pending Payment state
        
        /** @var \Magento\Sales\Model\Order\Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => 'pending_payupl',
            'label' => 'Pending Payu.pl'
        ])->save();
        $status->assignState('pending_payment', false, true);
    }
}
