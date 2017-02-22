<?php
/**
 * @copyright Copyright (c) 2017 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;

/**
 * Class created to simulate/emulate the response from PayU
 *
 * @package Orba\Payupl\Console\Command
 */
class EmulateNotificationCommand extends Command
{
    /**
     * PayU.pl order number (param: session_id)
     */
    const ARG_NAME_PAYUPL_ORDER_ID = 'payupl_order_id';

    /**
     * Order amount in base currency
     */
    const ARG_NAME_AMOUNT = 'amount';

    /**
     * Integer number representing transaction status from PayU.pl
     * For available Api status codes
     * @see \Orba\Payupl\Model\Client\Classic\Order
     * @see \Orba\Payupl\Model\Client\Rest\Order
     */
    const ARG_NAME_STATUS = 'status';

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var \Orba\Payupl\Helper\Command
     */
    protected $commandHelper;

    /**
     * EmulateNotificationCommand constructor.
     * @param AppState $appState
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Orba\Payupl\Helper\Command $commandHelper
     */
    public function __construct(
        AppState $appState,
        \Magento\Store\Model\App\Emulation $emulation,
        \Orba\Payupl\Helper\Command $commandHelper
    )
    {
        $this->appState = $appState;
        $this->emulation = $emulation;
        $this->commandHelper = $commandHelper;
        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('payupl:emulate:notification')
            ->setDescription('Emulate notification process for given PayU.pl order ID')
            ->setDefinition([
                new InputArgument(
                    self::ARG_NAME_PAYUPL_ORDER_ID,
                    InputArgument::REQUIRED,
                    'PayU.pl order ID'
                ),
                new InputArgument(
                    self::ARG_NAME_STATUS,
                    InputArgument::REQUIRED,
                    'PayU.pl status value'
                ),
                new InputArgument(
                    self::ARG_NAME_AMOUNT,
                    InputArgument::OPTIONAL,
                    'Amount of order. Note: all amounts should be given in the smallest unit for a given currency. In Example 10 PLN => 1000',
                    'auto'
                ),
            ]);
        parent::configure();
    }

    /**
     * Emulate response from PayU - notification process will go
     *
     * Usage: bin/magento payupl:emulate:notification <payu_order_id> <status_code> <amount>
     * <payu_order_id>      => like 000000012:1487247404:1
     * <status_code>        =>
     * @see \Orba\Payupl\Model\Client\Classic\Order
     * @see \Orba\Payupl\Model\Client\Rest\Order
     * <amount>             => 'auto' (amount taken from order, leave empty for auto) or specific value
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(AppArea::AREA_FRONTEND);
            // By PayU order ID
            $payuplOrderId = $this->commandHelper->getPayuplOrderId($input->getArgument(self::ARG_NAME_PAYUPL_ORDER_ID));
            // load order
            $order = $this->commandHelper->getOrderByPayuplOrderId($payuplOrderId);
            // Emulate store for proper work
            $this->emulation->startEnvironmentEmulation($order->getStoreId());
            // Get status
            $status = $this->commandHelper->getStatus($input->getArgument(self::ARG_NAME_STATUS));
            // And amount
            $amount = $this->commandHelper->getAmount($input->getArgument(self::ARG_NAME_AMOUNT), $order);
            // order object for api client
            $orderHelper = $this->commandHelper->getClient()->getOrderHelper();

            $output->writeln(sprintf('Order increment ID: %s (entity ID: %s)', $order->getIncrementId(), $order->getId()));
            // Emulate notification process
            if ($orderHelper->canProcessNotification($payuplOrderId)) {
                $orderHelper->processNotification($payuplOrderId, $status, $amount);
                $output->writeln('Notification process go through successfully');
            } else {
                $output->writeln('Can not process notification');
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $this->emulation->stopEnvironmentEmulation();
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
