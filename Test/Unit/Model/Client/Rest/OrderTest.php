<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Orba\Payupl\Model\Client\Exception;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Order
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataGetter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_methodCaller;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dataValidator = $this->getMockBuilder(Order\DataValidator::class)->getMock();
        $this->_dataGetter = $this->getMockBuilder(Order\DataGetter::class)->disableOriginalConstructor()->getMock();
        $this->_methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            Order::class,
            [
                'dataValidator' => $this->_dataValidator,
                'dataGetter' => $this->_dataGetter,
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
        $this->_dataGetter->expects($this->once())->method('getContinueUrl');
        $this->_dataGetter->expects($this->once())->method('getNotifyUrl');
        $this->_dataGetter->expects($this->once())->method('getCustomerIp');
        $this->_dataGetter->expects($this->once())->method('getMerchantPosId');
        $extendedData = $this->_model->addSpecialData($data);
        $this->assertEquals($data, array_intersect($extendedData, $data));
        $this->assertArrayHasKey('continueUrl', $extendedData);
        $this->assertArrayHasKey('notifyUrl', $extendedData);
        $this->assertArrayHasKey('customerIp', $extendedData);
        $this->assertArrayHasKey('merchantPosId', $extendedData);
    }

    public function testGetDataForOrderCreateSuccessNoBuyer()
    {
        $productsData = ['products'];
        $shippingData = ['shipping'];
        $buyerData = null;
        $basicData = ['basic'];
        $order = $this->_getOrderMock();
        $this->_preTestGetDataForOrderCreateSuccess($order, $productsData, $shippingData, $buyerData, $basicData);
        $productsData[] = $shippingData;
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData]
            ),
            $this->_model->getDataForOrderCreate($order)
        );
    }

    public function testGetDataForOrderCreateSuccessNoShipping()
    {
        $productsData = ['products'];
        $shippingData = null;
        $buyerData = ['buyer'];
        $basicData = ['basic'];
        $order = $this->_getOrderMock();
        $this->_preTestGetDataForOrderCreateSuccess($order, $productsData, $shippingData, $buyerData, $basicData);
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData],
                ['buyer' => $buyerData]
            ),
            $this->_model->getDataForOrderCreate($order)
        );
    }

    public function testGetDataForOrderCreateSuccessAllData()
    {
        $productsData = ['products'];
        $shippingData = ['shipping'];
        $buyerData = ['buyer'];
        $basicData = ['basic'];
        $order = $this->_getOrderMock();
        $this->_preTestGetDataForOrderCreateSuccess($order, $productsData, $shippingData, $buyerData, $basicData);
        $productsData[] = $shippingData;
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData],
                ['buyer' => $buyerData]
            ),
            $this->_model->getDataForOrderCreate($order)
        );
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
        $data = ['extOrderId' => '123'];
        $result = $this->_getResultMock();
        $response = new \stdClass();
        $response->orderId = '456';
        $response->redirectUri = 'http://redirect.uri';
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCreate'),
            $this->equalTo([$data])
        )->willReturn($result);
        $returnArray = [
            'orderId' => $response->orderId,
            'redirectUri' => $response->redirectUri,
            'extOrderId' => $data['extOrderId']
        ];
        $this->assertEquals($returnArray, $this->_model->create($data));
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
        $result = $this->_getResultMock();
        $response = new \stdClass();
        $response->status = 'COMPLETED';
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $status = $response->status;
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderRetrieve'),
            $this->equalTo([$id])
        )->willReturn($result);
        $this->assertEquals($status, $this->_model->retrieve($id));
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
        $result = $this->_getResultMock();
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCancel'),
            $this->equalTo([$id])
        )->willReturn($result);
        $this->assertTrue($this->_model->cancel($id));
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
        $result = $this->_getResultMock();
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderStatusUpdate'),
            $this->equalTo([$data])
        )->willReturn($result);
        $this->assertTrue($this->_model->statusUpdate($data));
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
        $result = $this->_getResultMock();
        $response = new \stdClass();
        $response->order = new \stdClass();
        $response->order->status = 'COMPLETED';
        $response->order->orderId = '123456';
        $resultArray = [
            'orderId' => $response->order->orderId,
            'status' => $response->order->status
        ];
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderConsumeNotification'),
            $this->equalTo([$data])
        )->willReturn($result);
        $this->assertEquals($resultArray, $this->_model->consumeNotification($data));
    }

    public function testGetNewStatus()
    {
        $this->assertEquals(Order::STATUS_NEW, $this->_model->getNewStatus());
    }

    protected function _getResultMock()
    {
        return $this->getMockBuilder(\OpenPayU_Result::class)->getMock();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param array $productsData
     * @param array|null $shippingData
     * @param array|null $buyerData
     * @param array $basicData
     */
    protected function _preTestGetDataForOrderCreateSuccess(\Magento\Sales\Model\Order $order, array $productsData, $shippingData, $buyerData, array $basicData)
    {
        $this->_dataGetter->expects($this->once())->method('getProductsData')->with($this->equalTo($order))->willReturn($productsData);
        $this->_dataGetter->expects($this->once())->method('getShippingData')->with($this->equalTo($order))->willReturn($shippingData);
        $this->_dataGetter->expects($this->once())->method('getBuyerData')->with($this->equalTo($order))->willReturn($buyerData);
        $this->_dataGetter->expects($this->once())->method('getBasicData')->with($this->equalTo($order))->willReturn($basicData);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock()
    {
        return $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
    }
}