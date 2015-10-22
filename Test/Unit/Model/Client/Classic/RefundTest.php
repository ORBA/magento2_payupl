<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Refund
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_methodCaller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderDataGetter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dataValidator = $this->getMockBuilder(Refund\DataValidator::class)->getMock();
        $this->_methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_orderDataGetter = $this->getMockBuilder(Order\DataGetter::class)->disableOriginalConstructor()->getMock();
        $this->_logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            Refund::class,
            [
                'dataValidator' => $this->_dataValidator,
                'methodCaller' => $this->_methodCaller,
                'transactionResource' => $this->_transactionResource,
                'orderDataGetter' => $this->_orderDataGetter,
                'logger' => $this->_logger
            ]
        );
    }

    public function testValidateCreateFailedEmptyOrderId()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validateCreate('', '', 0));
    }

    public function testValidateCreateFailedEmptyDescription()
    {
        $orderId = '123456';
        $this->_dataValidator->method('validateEmpty')->will($this->onConsecutiveCalls(true, false));
        $this->assertFalse($this->_model->validateCreate($orderId, '', 0));
    }

    public function testValidateCreateFailedInvalidAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = 'invalid';
        $this->_expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->_dataValidator->expects($this->once())->method('validatePositiveInt')->willReturn(false);
        $this->assertFalse($this->_model->validateCreate($orderId, $description, $amount));
    }

    public function testValidateCreateFailedNoAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $this->_expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->assertFalse($this->_model->validateCreate($orderId, $description));
    }

    public function testValidateCreateSuccessWithAmount()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = 100;
        $this->_expectNotEmptyOrderIdAndDescription($orderId, $description);
        $this->_dataValidator->expects($this->once())->method('validatePositiveInt')->with($this->equalTo($amount))->willReturn(true);
        $this->assertTrue($this->_model->validateCreate($orderId, $description, $amount));
    }

    public function testCreateFailExtOrderIdNotFound()
    {
        $payuplOrderId = '123456';
        $description = 'Description';
        $amount = 100;
        $this->_transactionResource->expects($this->once())->method('getExtOrderIdByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn(false);
        $this->assertFalse($this->_model->create($payuplOrderId, $description, $amount));
    }

    public function testCreateFailGet()
    {
        $payuplOrderId = '123456';
        $description = 'Description';
        $amount = 100;
        $realPayuplOrderId = '345678';
        $posId = '987654';
        $ts = 123;
        $sig = 'abc123';
        $authData = $this->_getAuthData($posId, $realPayuplOrderId, $ts, $sig);
        $this->_transactionResource->expects($this->once())->method('getExtOrderIdByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn($realPayuplOrderId);
        $this->_orderDataGetter->expects($this->once())->method('getPosId')->willReturn($posId);
        $this->_orderDataGetter->expects($this->once())->method('getTs')->willReturn($ts);
        $this->_orderDataGetter->expects($this->once())->method('getSigForOrderRetrieve')->with($this->equalTo([
            'pos_id' => $posId,
            'session_id' => $realPayuplOrderId,
            'ts' => $ts
        ]))->willReturn($sig);
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('refundGet'),
            $this->equalTo([$authData])
        )->willReturn(false);
        $this->assertFalse($this->_model->create($payuplOrderId, $description, $amount));
    }

    public function testCreateFailAdd()
    {
        $payuplOrderId = '123456';
        $description = 'Description';
        $amount = 100;
        $this->_expectRefundAddResult($amount, $description, $payuplOrderId, false);
        $this->assertFalse($this->_model->create($payuplOrderId, $description, $amount));
    }

    public function testCreateFailStatus()
    {
        $payuplOrderId = '123456';
        $description = 'Description';
        $amount = 100;
        $error = -5;
        $this->_expectRefundAddResult($amount, $description, $payuplOrderId, $error);
        $this->_logger->expects($this->once())->method('error')->with('Refund error ' . $error . ' for transaction ' . $payuplOrderId);
        $this->assertFalse($this->_model->create($payuplOrderId, $description, $amount));
    }

    public function testCreateSuccess()
    {
        $payuplOrderId = '123456';
        $description = 'Description';
        $amount = 100;
        $this->_expectRefundAddResult($amount, $description, $payuplOrderId, 0);
        $this->assertTrue($this->_model->create($payuplOrderId, $description, $amount));
    }

    /**
     * @param string $orderId
     * @param string $description
     */
    protected function _expectNotEmptyOrderIdAndDescription($orderId, $description)
    {
        $this->_dataValidator->method('validateEmpty')->withConsecutive(
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
    protected function _getAuthData($posId, $realPayuplOrderId, $ts, $sig)
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
    protected function _getGetResult($refsHash)
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
    protected function _getAddData($refsHash, $amount, $description)
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
     * @param $payuplOrderId
     * @param $value
     */
    protected function _expectRefundAddResult($amount, $description, $payuplOrderId, $value)
    {
        $realPayuplOrderId = '345678';
        $posId = '987654';
        $ts = 123;
        $sig = 'abc123';
        $authData = $this->_getAuthData($posId, $realPayuplOrderId, $ts, $sig);
        $refsHash = 'abc';
        $getResult = $this->_getGetResult($refsHash);
        $addData = $this->_getAddData($refsHash, $amount, $description);
        $this->_transactionResource->expects($this->once())->method('getExtOrderIdByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn($realPayuplOrderId);
        $this->_orderDataGetter->expects($this->once())->method('getPosId')->willReturn($posId);
        $this->_orderDataGetter->expects($this->once())->method('getTs')->willReturn($ts);
        $this->_orderDataGetter->expects($this->once())->method('getSigForOrderRetrieve')->with($this->equalTo([
            'pos_id' => $posId,
            'session_id' => $realPayuplOrderId,
            'ts' => $ts
        ]))->willReturn($sig);
        $this->_methodCaller->expects($this->at(0))->method('call')->with(
            $this->equalTo('refundGet'),
            $this->equalTo([$authData])
        )->willReturn($getResult);
        $this->_methodCaller->expects($this->at(1))->method('call')->with(
            $this->equalTo('refundAdd'),
            $this->equalTo([$authData, $addData])
        )->willReturn($value);
    }

}