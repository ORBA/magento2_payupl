<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

class NotifyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resultForwardFactory;

    /**
     * @var Notify
     */
    protected $_controller;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->_clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)->disableOriginalConstructor()->getMock();
        $this->_resultForwardFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\ForwardFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_controller = $objectManager->getObject(Notify::class, [
            'context' => $this->_context,
            'clientFactory' => $this->_clientFactory,
            'resultForwardFactory' => $this->_resultForwardFactory
        ]);
    }

    public function testIgnoreNotification()
    {
        $response = [
            'payuplOrderId' => 'ABC',
            'status' => 'COMPLETED'
        ];
        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()->getMock();
        $this->_context->expects($this->once())->method('getRequest')->willReturn($request);
        $client = $this->_getClientMock();
        $client->expects($this->once())->method('orderConsumeNotification')->with($request)->willReturn($response);
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->disableOriginalConstructor()->getMock();
        $clientOrderHelper->expects($this->once())->method('canProcessNotification')->with($response['payuplOrderId'])->willReturn(false);
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $resultForward = $this->getMockBuilder(\Magento\Framework\Controller\Result\Forward::class)->disableOriginalConstructor()->getMock();
        $resultForward->expects($this->once())->method('forward')->with('noroute');
        $this->_resultForwardFactory->expects($this->once())->method('create')->willReturn($resultForward);
        $this->assertEquals($resultForward, $this->_controller->execute());
    }

    public function testProcessNotification()
    {
        $response = [
            'payuplOrderId' => 'ABC',
            'status' => 'COMPLETED',
            'amount' => 2.22
        ];
        $result = 'result';
        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()->getMock();
        $this->_context->expects($this->once())->method('getRequest')->willReturn($request);
        $client = $this->_getClientMock();
        $client->expects($this->once())->method('orderConsumeNotification')->with($request)->willReturn($response);
        $clientOrderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\OrderInterface::class)->disableOriginalConstructor()->getMock();
        $clientOrderHelper->expects($this->once())->method('canProcessNotification')->with($response['payuplOrderId'])->willReturn(true);
        $clientOrderHelper->expects($this->once())->method('processNotification')->with(
            $this->equalTo($response['payuplOrderId']),
            $this->equalTo($response['status'])
        )->willReturn($result);
        $client->expects($this->once())->method('getOrderHelper')->willReturn($clientOrderHelper);
        $this->assertEquals($result, $this->_controller->execute());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->_clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }
}