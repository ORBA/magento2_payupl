<?php

namespace Orba\Payupl\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;

/**
 * Retrieve information about PayU.pl transaction for given PayU order ID
 *
 * @package Orba\Payupl\Console\Command
 */
class RetrieveCommand extends Command
{

    /**
     * Magento order incremented ID
     */
    const ARG_NAME_ORDER_ID = 'order';

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $resourceTransaction;

    /**
     * @var \Orba\Payupl\Helper\Command
     */
    protected $commandHelper;

    /**
     * RetrieveCommand constructor.
     * @param AppState $appState
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Orba\Payupl\Model\ResourceModel\Transaction $resourceTransaction
     * @param \Orba\Payupl\Helper\Command $commandHelper
     */
    public function __construct(
        AppState $appState
        , \Magento\Store\Model\App\Emulation $emulation
        , \Orba\Payupl\Model\ResourceModel\Transaction $resourceTransaction
        , \Orba\Payupl\Helper\Command $commandHelper
    )
    {
        $this->appState = $appState;
        $this->emulation = $emulation;
        $this->resourceTransaction = $resourceTransaction;
        $this->commandHelper = $commandHelper;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('payupl:retrieve')
            ->setDescription('Retrieve information about PayU.pl transaction for given order')
            ->setDefinition([
                new InputArgument(
                    self::ARG_NAME_ORDER_ID,
                    InputArgument::REQUIRED,
                    'Magento order incremented ID'
                )
            ]);
        parent::configure();
    }

    /**
     * Retrieve data from PayU for given <order_increment_id>
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(AppArea::AREA_FRONTEND);
            // Get magento order increment ID
            $orderIncrementId = $this->commandHelper->getOrderIncrementId($input->getArgument(self::ARG_NAME_ORDER_ID));
            // Load by this ID the order object
            $order = $this->commandHelper->getOrderByOrderIncrementId($orderIncrementId);
            // Emulate store for proper work
            $this->emulation->startEnvironmentEmulation($order->getStoreId());

            // Get PayU.pl order numbers from order and check if is valid
            $client = $this->commandHelper->getClient();
            // order object for api client
            $orderHelper = $client->getOrderHelper();

            $output->writeln(sprintf('Order increment ID: %s (entity ID: %s)', $orderIncrementId, $order->getId()));
            $allPayuplOrderIds = $this->resourceTransaction->getAllPayuplOrderIdsByOrderId($order->getId());
            foreach ($allPayuplOrderIds as $payuplOrderId) {
                // Get data from PayU.pl API
                $result = $client->orderRetrieve($payuplOrderId);
                $output->writeln(sprintf('PayU order ID: %s', $payuplOrderId));
                $output->writeln(sprintf('       Status: %s (%s)', $result['status'], $orderHelper->getStatusDescription($result['status'])));
                $output->writeln(sprintf('       Amount: %s', $result['amount']));
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $this->emulation->stopEnvironmentEmulation();
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
