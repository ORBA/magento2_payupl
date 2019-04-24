<?php
/**
 * @copyright Copyright (c) 2017 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Console\Command;

use Magento\Framework\Phrase;

use Magento\Store\Model\App\Emulation;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;

use Symfony\Component\Console\Tester\CommandTester;

class RetrieveCommandTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Magento\Framework\App\State | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    /**
     * @var \Magento\Store\Model\App\Emulation | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emulation;

    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceTransaction;

    /**
     * @var \Orba\Payupl\Helper\Command | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandHelper;

    /**
     * @var \Orba\Payupl\Console\Command\RetrieveCommand | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $command;

    /**
     * Is called before running a test
     */
    public function setUp()
    {
        parent::setUp();
        $this->appState = $this->basicMock(AppState::class);
        $this->emulation = $this->basicMock(Emulation::class);
        $this->resourceTransaction = $this->basicMock(\Orba\Payupl\Model\ResourceModel\Transaction::class);
        $this->commandHelper = $this->basicMock(\Orba\Payupl\Helper\Command::class);

        $this->command = $this->objectManager->getObject(RetrieveCommand::class, [
            'appState' => $this->appState,
            'emulation' => $this->emulation,
            'resourceTransaction' => $this->resourceTransaction,
            'commandHelper' => $this->commandHelper
        ]);
    }

    public function testConfiguration()
    {
        $this->assertSame($this->command->getName(), 'payupl:retrieve');
        $this->assertSame($this->command->getDescription(), 'Retrieve information about Payu.pl transaction for given order');
    }

    public function testExecute()
    {
        $orderIncrementId = "000000001";
        $storeId = 1;
        $orderId = 1;

        $this->appState->method("setAreaCode")->with(AppArea::AREA_FRONTEND);
        $order = $this->basicMock(\Orba\Payupl\Model\Sales\Order::class);
        $order->method('getStoreId')->willReturn($storeId);
        $order->method('getId')->willReturn($orderId);
        $order->method('getIncrementId')->willReturn($orderIncrementId);

        $orderHelper = $this->basicMock(\Orba\Payupl\Model\Client\OrderInterface::class);
        $orderHelper->method("getStatusDescription")->willReturn("status description");

        $client = $this->basicMock(\Orba\Payupl\Model\Client::class);
        $client->method("getOrderHelper")->willReturn($orderHelper);
        $client->method("orderRetrieve")->withAnyParameters()->willReturnOnConsecutiveCalls(
            ['status' => 1, 'amount' => 1000],
            ['status' => 2, 'amount' => 1000]
        );

        $this->emulation->method("startEnvironmentEmulation")->with($storeId);
        $this->emulation->method("stopEnvironmentEmulation");

        $this->commandHelper->method('getOrderIncrementId')->with($orderIncrementId)->willReturn($orderIncrementId);
        $this->commandHelper->method('getOrderByOrderIncrementId')->with($orderIncrementId)->willReturn($order);
        $this->commandHelper->method('getClient')->willReturn($client);

        $this->resourceTransaction->method("getAllPayuplOrderIdsByOrderId")->with($orderId)->willReturn(['aaa', 'bbb']);

        $commandTester = new CommandTester($this->command);
        $input = [RetrieveCommand::ARG_NAME_ORDER_ID => $orderIncrementId];
        $commandTester->execute($input);
        $this->assertEquals(\Magento\Framework\Console\Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());

        $client->method("orderRetrieve")->withAnyParameters()->willReturnOnConsecutiveCalls([],[]);
        $this->resourceTransaction->method("getAllPayuplOrderIdsByOrderId")->with($orderId)->willReturn([]);
        $commandTester->execute($input);
        $this->assertEquals(\Magento\Framework\Console\Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertNotEmpty($commandTester->getDisplay());

        $this->commandHelper->method('getOrderIncrementId')->withAnyParameters()->willThrowException(new \InvalidArgumentException());
        $commandTester->execute($input);
        $this->assertEquals(\Magento\Framework\Console\Cli::RETURN_FAILURE, $commandTester->getStatusCode());

        $this->setExpectedException(\RuntimeException::class, 'Not enough arguments.');
        $commandTester->execute([]);

        return $commandTester;
    }

}
