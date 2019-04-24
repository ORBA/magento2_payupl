<?php
/**
 * @copyright Copyright (c) 2017 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Console\Command;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;
use Magento\Framework\Console\Cli as Cli;
use Magento\Framework\Phrase;
use Magento\Store\Model\App\Emulation\Proxy as Emulation;
use Symfony\Component\Console\Tester\CommandTester;

class EmulateNotificationCommandTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    /**
     * @var Emulation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emulation;

    /**
     * @var \Orba\Payupl\Helper\Command|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandHelper;

    /**
     * @var EmulateNotificationCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $command;

    /**
     * Is called before running a test
     */
    public function setUp()
    {
        parent::setUp();
        $this->appState  = $this->basicMock(AppState::class);
        $this->emulation = $this->getMockBuilder(Emulation::class)->disableOriginalConstructor()
            ->setMethods(['startEnvironmentEmulation', 'stopEnvironmentEmulation'])->getMock();
        $this->commandHelper = $this->basicMock(\Orba\Payupl\Helper\Command::class);

        $this->command = $this->objectManager->getObject(EmulateNotificationCommand::class, [
            'appState' => $this->appState,
            'emulation' => $this->emulation,
            'commandHelper' => $this->commandHelper
        ]);
    }

    public function testConfiguration()
    {
        $this->assertSame($this->command->getName(), 'payupl:emulate-notification');
        $this->assertSame($this->command->getDescription(), 'Emulate notification process for given Payu.pl order ID');
    }

    public function testExecuteSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'NEW';
        $amount = '1000';
        $storeId = 1;
        $incrementId = "000000001";
        $orderId = 1;

        $order = $this->basicMock(\Orba\Payupl\Model\Sales\Order::class);
        $orderHelper = $this->basicMock(\Orba\Payupl\Model\Client\OrderInterface::class);
        $client = $this->basicMock(\Orba\Payupl\Model\Client::class);

        $order->method('getStoreId')->willReturn($storeId);
        $order->method('getId')->willReturn($orderId);
        $order->method('getIncrementId')->willReturn($incrementId);
        $client->method("getOrderHelper")->willReturn($orderHelper);
        $orderHelper->method("canProcessNotification")->with($payuplOrderId)->willReturnOnConsecutiveCalls(true, false);
        $orderHelper->method("processNotification")->with($payuplOrderId, $status, $amount);

        $this->appState->method("setAreaCode")->with(AppArea::AREA_FRONTEND);
        $this->commandHelper->method('getPayuplOrderId')->with($payuplOrderId)->willReturn($payuplOrderId);
        $this->commandHelper->method('getOrderByPayuplOrderId')->with($payuplOrderId)->willReturn($order);
        $this->commandHelper->method('getStatus')->with($status)->willReturn($status);
        $this->commandHelper->method('getAmount')->with($amount)->willReturn($amount);
        $this->commandHelper->method('getClient')->willReturn($client);

        $this->emulation->method("startEnvironmentEmulation")->with($storeId);
        $this->emulation->method("stopEnvironmentEmulation");

        $commandTester = new CommandTester($this->command);
        $input = [
            EmulateNotificationCommand::ARG_NAME_PAYUPL_ORDER_ID => $payuplOrderId,
            EmulateNotificationCommand::ARG_NAME_STATUS => $status,
            EmulateNotificationCommand::ARG_NAME_AMOUNT => $amount
        ];
        $commandTester->execute($input);
        $this->assertEquals(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());

        return $commandTester;
    }

    public function testExecuteByWrongArg()
    {
        $commandTester = $this->testExecuteSuccess();
        $input = $commandTester->getInput();
        $this->commandHelper->method('getOrderByPayuplOrderId')->willThrowException(new \Magento\Framework\Exception\NotFoundException(new Phrase('')));
        $commandTester->execute($input->getArguments());
        $this->assertEquals(Cli::RETURN_FAILURE, $commandTester->getStatusCode());
    }
}
