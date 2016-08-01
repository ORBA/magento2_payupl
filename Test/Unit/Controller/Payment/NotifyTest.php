<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class NotifyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Notify
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultForwardFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\ForwardFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()
            ->getMock();
        $this->controller = $objectManager->getObject(Notify::class, [
            'context' => $this->context,
            'clientFactory' => $this->clientFactory,
            'resultForwardFactory' => $this->resultForwardFactory,
            'logger' => $this->logger
        ]);
    }

    public function testIgnoreNotificationClientException()
    {
        $exception = new LocalizedException(new Phrase('Exception'));
        $this->clientFactory->expects($this->once())->method('create')->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $resultForward = $this->getMockBuilder(\Magento\Framework\Controller\Result\Forward::class)
            ->disableOriginalConstructor()->getMock();
        $resultForward->expects($this->once())->method('forward')->with('noroute');
        $this->resultForwardFactory->expects($this->once())->method('create')->willReturn($resultForward);
        $this->assertEquals($resultForward, $this->controller->execute());
    }

    public function testIgnoreNotificationInvalidOrder()
    {
        $response = [
            'payuplOrderId' => 'ABC',
            'status' => 'COMPLETED'
        ];
        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())->method('getRequest')->willReturn($request);
        $client = $this->getClientMock();
        $client->expects($this->once())->method('orderConsumeNotification')->with($request)->willReturn($response);
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)
            ->disableOriginalConstructor()->getMock();
        $clientOrderHelper->expects($this->once())->method('canProcessNotification')->with($response['payuplOrderId'])
            ->willReturn(false);
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $resultForward = $this->getMockBuilder(\Magento\Framework\Controller\Result\Forward::class)
            ->disableOriginalConstructor()->getMock();
        $resultForward->expects($this->once())->method('forward')->with('noroute');
        $this->resultForwardFactory->expects($this->once())->method('create')->willReturn($resultForward);
        $this->assertEquals($resultForward, $this->controller->execute());
    }

    public function testProcessNotification()
    {
        $response = [
            'payuplOrderId' => 'ABC',
            'status' => 'COMPLETED',
            'amount' => 2.22
        ];
        $result = 'result';
        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())->method('getRequest')->willReturn($request);
        $client = $this->getClientMock();
        $client->expects($this->once())->method('orderConsumeNotification')->with($request)->willReturn($response);
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)
            ->disableOriginalConstructor()->getMock();
        $clientOrderHelper->expects($this->once())->method('canProcessNotification')->with($response['payuplOrderId'])
            ->willReturn(true);
        $clientOrderHelper->expects($this->once())->method('processNotification')->with(
            $this->equalTo($response['payuplOrderId']),
            $this->equalTo($response['status'])
        )->willReturn($result);
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $this->assertEquals($result, $this->controller->execute());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }
}
