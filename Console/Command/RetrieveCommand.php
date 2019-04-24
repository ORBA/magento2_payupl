<?php

namespace Orba\Payupl\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
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
 * Retrieve information about Payu.pl transaction for given Payu.pl order ID
 *
 * @package Orba\Payupl\Console\Command
 */
class RetrieveCommand extends Command
{

    /**
     * Magento order increment ID
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
        AppState $appState,
        \Magento\Store\Model\App\Emulation $emulation,
        \Orba\Payupl\Model\ResourceModel\Transaction $resourceTransaction,
        \Orba\Payupl\Helper\Command $commandHelper
    )
    {
        $this->appState = $appState;
        $this->emulation = $emulation;
        $this->resourceTransaction = $resourceTransaction;
        $this->commandHelper = $commandHelper;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('payupl:retrieve')
            ->setDescription('Retrieve information about Payu.pl transaction for given order')
            ->setDefinition([
                new InputArgument(
                    self::ARG_NAME_ORDER_ID,
                    InputArgument::REQUIRED,
                    'Magento order increment ID'
                )
            ]);
        parent::configure();
    }

    /**
     * Retrieve data from Payu.pl for given <order_increment_id>
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = Cli::RETURN_FAILURE;
        try {
            $this->appState->setAreaCode(AppArea::AREA_FRONTEND);
            $orderIncrementId = $this->commandHelper->getOrderIncrementId($input->getArgument(self::ARG_NAME_ORDER_ID));
            $order = $this->commandHelper->getOrderByOrderIncrementId($orderIncrementId);
            $this->emulation->startEnvironmentEmulation($order->getStoreId());
            $client = $this->commandHelper->getClient();
            $orderHelper = $client->getOrderHelper();
            $output->writeln(sprintf('Order increment ID: %s (entity ID: %s)', $orderIncrementId, $order->getId()));
            $allPayuplOrderIds = $this->resourceTransaction->getAllPayuplOrderIdsByOrderId($order->getId());
            foreach ($allPayuplOrderIds as $payuplOrderId) {
                try {
                    $output->writeln(sprintf('Payu order ID: %s', $payuplOrderId));
                    $result = $client->orderRetrieve($payuplOrderId);
                    $output->writeln(sprintf('       Status: %s (%s)', $result['status'], $orderHelper->getStatusDescription($result['status'])));
                    $output->writeln(sprintf('       Amount: %s', $result['amount']));
                } catch (LocalizedException $e) {
                    $output->writeln($e->getMessage());
                }
            }
            $result = Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        $this->emulation->stopEnvironmentEmulation();
        return $result;
    }
}
