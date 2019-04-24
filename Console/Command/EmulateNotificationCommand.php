<?php
/**
 * @copyright Copyright (c) 2017 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Console\Command;

use Magento\Framework\Console\Cli as Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\App\Emulation\Proxy as Emulation;
use Orba\Payupl\Helper\Command as CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;

/**
 * Class created to emulate the response from Payu.pl
 *
 * @package Orba\Payupl\Console\Command
 */
class EmulateNotificationCommand extends Command
{
    /**
     * Payu.pl order number (param: session_id)
     */
    const ARG_NAME_PAYUPL_ORDER_ID = 'payupl_order_id';

    /**
     * Order amount in base currency
     */
    const ARG_NAME_AMOUNT = 'amount';

    /**
     * Integer number representing transaction status from Payu.pl
     * For available Api status codes
     * @see \Orba\Payupl\Model\Client\Classic\Order
     * @see \Orba\Payupl\Model\Client\Rest\Order
     */
    const ARG_NAME_STATUS = 'status';

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var CommandHelper
     */
    private $commandHelper;

    /**
     * EmulateNotificationCommand constructor.
     * @param AppState $appState
     * @param Emulation $emulation
     * @param CommandHelper $commandHelper
     */
    public function __construct(
        AppState $appState,
        Emulation $emulation,
        CommandHelper $commandHelper
    )
    {
        $this->appState = $appState;
        $this->emulation = $emulation;
        $this->commandHelper = $commandHelper;
        parent::__construct();
    }


    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('payupl:emulate-notification')
            ->setDescription('Emulate notification process for given Payu.pl order ID')
            ->setDefinition([
                new InputArgument(
                    self::ARG_NAME_PAYUPL_ORDER_ID,
                    InputArgument::REQUIRED,
                    'Payu.pl order ID'
                ),
                new InputArgument(
                    self::ARG_NAME_STATUS,
                    InputArgument::REQUIRED,
                    'Payu.pl status value'
                ),
                new InputArgument(
                    self::ARG_NAME_AMOUNT,
                    InputArgument::OPTIONAL,
                    'Transaction amount',
                    'auto'
                ),
            ]);
        parent::configure();
    }

    /**
     * Emulate response from Payu.pl
     *
     * Usage: bin/magento payupl:emulate-notification <payupl_order_id> <status_code> <amount>
     * <payu_order_id>      => like 000000012:1487247404:1
     * <status_code>        =>
     * @see \Orba\Payupl\Model\Client\Classic\Order
     * @see \Orba\Payupl\Model\Client\Rest\Order
     * <amount>             => 'auto' (amount taken from order, this is a default value) or specific value (float)
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
            $payuplOrderId = $this->commandHelper->getPayuplOrderId($input->getArgument(self::ARG_NAME_PAYUPL_ORDER_ID));
            $order = $this->commandHelper->getOrderByPayuplOrderId($payuplOrderId);
            $this->emulation->startEnvironmentEmulation($order->getStoreId());
            $status = $this->commandHelper->getStatus($input->getArgument(self::ARG_NAME_STATUS));
            $amount = $this->commandHelper->getAmount($input->getArgument(self::ARG_NAME_AMOUNT), $order);
            $orderHelper = $this->commandHelper->getClient()->getOrderHelper();
            $output->writeln(sprintf('Order increment ID: %s (entity ID: %s)', $order->getIncrementId(), $order->getId()));
            if ($orderHelper->canProcessNotification($payuplOrderId)) {
                $orderHelper->processNotification($payuplOrderId, $status, $amount);
                $output->writeln('Notification processed successfully');
                $result = Cli::RETURN_SUCCESS;
            } else {
                $output->writeln('Cannot process notification');
            }
        } catch (LocalizedException $e) {
            $output->writeln($e->getMessage());
        }
        $this->emulation->stopEnvironmentEmulation();
        return $result;
    }
}
