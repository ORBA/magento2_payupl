<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client\Order
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataAdder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_methodCaller;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dataValidator = $this->getMockBuilder(Order\DataValidator::class)->getMock();
        $this->_dataAdder = $this->getMockBuilder(Order\DataAdder::class)->disableOriginalConstructor()->getMock();
        $this->_methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            Order::class,
            [
                'dataValidator' => $this->_dataValidator,
                'dataAdder' => $this->_dataAdder,
                'methodCaller' => $this->_methodCaller
            ]
        );
    }

    public function testValidateCreateFailedEmpty()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validateCreate());
    }

    public function testValidateCreateFailedInvalidBasicData()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateBasicData')->willReturn(false);
        $this->assertFalse($this->_model->validateCreate());
    }

    public function testValidateCreateFailedInvalidProductsData()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateBasicData')->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateProductsData')->willReturn(false);
        $this->assertFalse($this->_model->validateCreate());
    }

    public function testValidateCreateSuccess()
    {
        $data = ['data'];
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->with($this->equalTo($data))->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateBasicData')->with($this->equalTo($data))->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateProductsData')->with($this->equalTo($data))->willReturn(true);
        $this->assertTrue($this->_model->validateCreate($data));
    }

    public function testDataAdder()
    {
        $data = [
            'example' => true
        ];
        $this->_dataAdder->expects($this->once())->method('getContinueUrl');
        $this->_dataAdder->expects($this->once())->method('getNotifyUrl');
        $this->_dataAdder->expects($this->once())->method('getCustomerIp');
        $this->_dataAdder->expects($this->once())->method('getMerchantPosId');
        $extendedData = $this->_model->addSpecialData($data);
        $this->assertEquals($data, array_intersect($extendedData, $data));
        $this->assertArrayHasKey('continueUrl', $extendedData);
        $this->assertArrayHasKey('notifyUrl', $extendedData);
        $this->assertArrayHasKey('customerIp', $extendedData);
        $this->assertArrayHasKey('merchantPosId', $extendedData);
    }

    public function testCreateFail()
    {
        $data = ['data'];
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCreate'),
            $this->equalTo([$data])
        )->willReturn(false);
        $this->assertFalse($this->_model->create($data));
    }

    public function testCreateSuccess()
    {
        $data = ['data'];
        $result = $this->_getResult();
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCreate'),
            $this->equalTo([$data])
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->create($data));
    }

    public function testValidateRetrieveFailedEmpty()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validateRetrieve(''));
    }

    public function testRetrieveFail()
    {
        $id = '123456';
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderRetrieve'),
            $this->equalTo([$id])
        )->willReturn(false);
        $this->assertFalse($this->_model->retrieve($id));
    }

    public function testRetrieveSuccess()
    {
        $id = '123456';
        $result = $this->_getResult();
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderRetrieve'),
            $this->equalTo([$id])
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->retrieve($id));
    }

    public function testValidateCancelFailedEmpty()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validateCancel(''));
    }

    public function testCancelFail()
    {
        $id = '123456';
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCancel'),
            $this->equalTo([$id])
        )->willReturn(false);
        $this->assertFalse($this->_model->cancel($id));
    }

    public function testCancelSuccess()
    {
        $id = '123456';
        $result = $this->_getResult();
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCancel'),
            $this->equalTo([$id])
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->cancel($id));
    }

    public function testValidateStatusUpdateFailedEmpty()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validateStatusUpdate());
    }

    public function testValidateStatusUpdateInvalidData()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateStatusUpdateData')->willReturn(false);
        $this->assertFalse($this->_model->validateStatusUpdate());
    }

    public function testStatusUpdateFail()
    {
        $data = ['data'];
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderStatusUpdate'),
            $this->equalTo([$data])
        )->willReturn(false);
        $this->assertFalse($this->_model->statusUpdate($data));
    }

    public function testStatusUpdateSuccess()
    {
        $data = ['data'];
        $result = $this->_getResult();
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderStatusUpdate'),
            $this->equalTo([$data])
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->statusUpdate($data));
    }

    public function testValidateConsumeNotificationFailedEmpty()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validateConsumeNotification());
    }

    public function testConsumeNotificationFail()
    {
        $data = ['data'];
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderConsumeNotification'),
            $this->equalTo([$data])
        )->willReturn(false);
        $this->assertFalse($this->_model->consumeNotification($data));
    }

    public function testConsumeNotificationSuccess()
    {
        $data = ['data'];
        $result = $this->_getResult();
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderConsumeNotification'),
            $this->equalTo([$data])
        )->willReturn($result);
        $this->assertEquals($result, $this->_model->consumeNotification($data));
    }

    protected function _getResult()
    {
        return $this->getMockBuilder(\OpenPayU_Result::class)->getMock();
    }

}