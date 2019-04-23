<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RefundTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Refund
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodCaller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderDataGetter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->dataValidator = $this->getMockBuilder(Refund\DataValidator::class)->getMock();
        $this->methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderDataGetter = $this->getMockBuilder(Order\DataGetter::class)->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            Refund::class,
            [
                'dataValidator' => $this->dataValidator,
                'methodCaller' => $this->methodCaller,
                'transactionResource' => $this->transactionResource,
                'orderDataGetter' => $this->orderDataGetter,
                'logger' => $this->logger
            ]
        );
    }

    public function testValidateCreateFailedEmptyOrderId()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->model->validateCreate('', '', 0));
    }

    public function testValidateCreateFailedEmptyDescription()
    {
        $orderId = '123456';
        $this->dataValidator->method('validateEmpty')->will($this->onConsecutiveCalls(true, false));
        $this->assertFalse($this->model->validateCreate($orderId, '', 0));
    }

    public function testValidateCreateFailedInvalidAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = 'invalid';
        $this->expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->dataValidator->expects($this->once())->method('validatePositiveInt')->willReturn(false);
        $this->assertFalse($this->model->validateCreate($orderId, $description, $amount));
    }

    public function testValidateCreateFailedNoAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $this->expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->assertFalse($this->model->validateCreate($orderId, $description));
    }

    public function testValidateCreateSuccessWithAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = 100;
        $this->expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->dataValidator->expects($this->once())->method('validatePositiveInt')->with($this->equalTo($amount))
            ->willReturn(true);
        $this->assertTrue($this->model->validateCreate($orderId, $description, $amount));
    }

    public function testCreateFailGet()
    {
        $description = 'Description';
        $amount = 100;
        $orderId = '345678';
        $posId = '987654';
        $ts = 123;
        $sig = 'abc123';
        $authData = $this->getAuthData($posId, $orderId, $ts, $sig);
        $this->orderDataGetter->expects($this->once())->method('getPosId')->willReturn($posId);
        $this->orderDataGetter->expects($this->once())->method('getTs')->willReturn($ts);
        $this->orderDataGetter->expects($this->once())->method('getSigForOrderRetrieve')->with($this->equalTo([
            'pos_id' => $posId,
            'session_id' => $orderId,
            'ts' => $ts
        ]))->willReturn($sig);
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('refundGet'),
            $this->equalTo([$authData])
        )->willReturn(false);
        $this->assertFalse($this->model->create($orderId, $description, $amount));
    }

    public function testCreateFailAdd()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = 100;
        $this->expectRefundAddResult($amount, $description, $orderId, false);
        $this->assertFalse($this->model->create($orderId, $description, $amount));
    }

    public function testCreateFailStatus()
    {
        $payuplOrderId = '123456';
        $description = 'Description';
        $amount = 100;
        $error = -5;
        $this->expectRefundAddResult($amount, $description, $payuplOrderId, $error);
        $this->logger->expects($this->once())->method('error')
            ->with('Refund error ' . $error . ' for transaction ' . $payuplOrderId);
        $this->assertFalse($this->model->create($payuplOrderId, $description, $amount));
    }

    public function testCreateSuccess()
    {
        $payuplOrderId = '123456';
        $description = 'Description';
        $amount = 100;
        $this->expectRefundAddResult($amount, $description, $payuplOrderId, 0);
        $this->assertTrue($this->model->create($payuplOrderId, $description, $amount));
    }

    /**
     * @param string $orderId
     * @param string $description
     */
    protected function expectNotEmptyOrderIdAndDescription($orderId, $description)
    {
        $this->dataValidator->method('validateEmpty')->withConsecutive(
            [$this->equalTo($orderId)],
            [$this->equalTo($description)]
        )->will($this->onConsecutiveCalls(true, true));
    }

    /**
     * @param $posId
     * @param $realPayuplOrderId
     * @param $ts
     * @param $sig
     * @return array
     */
    protected function getAuthData($posId, $realPayuplOrderId, $ts, $sig)
    {
        return [
            'posId' => $posId,
            'sessionId' => $realPayuplOrderId,
            'ts' => $ts,
            'sig' => $sig
        ];
    }

    /**
     * @param $refsHash
     * @return object
     */
    protected function getGetResult($refsHash)
    {
        return (object)[
            'refsHash' => $refsHash
        ];
    }

    /**
     * @param $refsHash
     * @param $amount
     * @param $description
     * @return array
     */
    protected function getAddData($refsHash, $amount, $description)
    {
        return [
            'refundsHash' => $refsHash,
            'amount' => $amount,
            'desc' => $description,
            'autoData' => true
        ];
    }

    /**
     * @param $amount
     * @param $description
     * @param $orderId
     * @param $value
     */
    protected function expectRefundAddResult($amount, $description, $orderId, $value)
    {
        $posId = '987654';
        $ts = 123;
        $sig = 'abc123';
        $authData = $this->getAuthData($posId, $orderId, $ts, $sig);
        $refsHash = 'abc';
        $getResult = $this->getGetResult($refsHash);
        $addData = $this->getAddData($refsHash, $amount, $description);
        $this->orderDataGetter->expects($this->once())->method('getPosId')->willReturn($posId);
        $this->orderDataGetter->expects($this->once())->method('getTs')->willReturn($ts);
        $this->orderDataGetter->expects($this->once())->method('getSigForOrderRetrieve')->with($this->equalTo([
            'pos_id' => $posId,
            'session_id' => $orderId,
            'ts' => $ts
        ]))->willReturn($sig);
        $this->methodCaller->expects($this->at(0))->method('call')->with(
            $this->equalTo('refundGet'),
            $this->equalTo([$authData])
        )->willReturn($getResult);
        $this->methodCaller->expects($this->at(1))->method('call')->with(
            $this->equalTo('refundAdd'),
            $this->equalTo([$authData, $addData])
        )->willReturn($value);
    }
}
