<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Orba\Payupl\Model\Client\Exception;
use Orba\Payupl\Model\Client\Rest\Order;

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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rawResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dataValidator = $this->getMockBuilder(Order\DataValidator::class)->getMock();
        $this->_dataGetter = $this->getMockBuilder(Order\DataGetter::class)->disableOriginalConstructor()->getMock();
        $this->_methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\Resource\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_orderProcessor = $this->getMockBuilder(Order\Processor::class)->disableOriginalConstructor()->getMock();
        $this->_rawResultFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $this->_model = $objectManagerHelper->getObject(
            Order::class,
            [
                'dataValidator' => $this->_dataValidator,
                'dataGetter' => $this->_dataGetter,
                'methodCaller' => $this->_methodCaller,
                'transactionResource' => $this->_transactionResource,
                'orderProcessor' => $this->_orderProcessor,
                'rawResultFactory' => $this->_rawResultFactory,
                'request' => $this->_request
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
        $extendedData = $this->_model->addSpecialDataToOrder($data);
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
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCreate'),
            $this->equalTo([$data])
        )->willReturn($response);
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

    public function testValidateRetrieveSuccess()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->assertTrue($this->_model->validateRetrieve('ABC'));
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
        $payuplOrderId = '123456';
        $response = (object) [
            'orders' => [
                0 => (object) [
                    'status' => Order::STATUS_COMPLETED,
                    'totalAmount' => '3200'
                ]
            ]
        ];
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderRetrieve'),
            $this->equalTo([$payuplOrderId])
        )->willReturn($response);
        $result = [
            'status' => $response->orders[0]->status,
            'amount' => $response->orders[0]->totalAmount / 100
        ];
        $this->assertEquals($result, $this->_model->retrieve($payuplOrderId));
    }

    public function testValidateCancelFailedEmpty()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->_model->validateCancel(''));
    }

    public function testValidateCancelSuccess()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->assertTrue($this->_model->validateCancel('ABC'));
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

    public function testValidateStatusUpdateSuccess()
    {
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateStatusUpdateData')->willReturn(true);
        $this->assertTrue($this->_model->validateStatusUpdate(['data']));
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

    public function testConsumeNotificationFailNoPost()
    {
        $request = $this->_getRequestMock();
        $request->expects($this->once())->method('isPost')->willReturn(false);
        $this->setExpectedException(Exception::class, 'POST request is required.');
        $this->_model->consumeNotification($request);
    }

    public function testConsumeNotificationFail()
    {
        $rawBody = 'body';
        $request = $this->_getRequestMock();
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->once())->method('getContent')->willReturn($rawBody);
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderConsumeNotification'),
            $this->equalTo([$rawBody])
        )->willReturn(false);
        $this->assertFalse($this->_model->consumeNotification($request));
    }

    public function testConsumeNotificationSuccess()
    {
        $rawBody = 'body';
        $request = $this->_getRequestMock();
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->once())->method('getContent')->willReturn($rawBody);
        $response = new \stdClass();
        $response->order = new \stdClass();
        $response->order->status = Order::STATUS_COMPLETED;
        $response->order->orderId = '123456';
        $response->order->totalAmount = 222;
        $resultArray = [
            'payuplOrderId' => $response->order->orderId,
            'status' => $response->order->status,
            'amount' => 2.22
        ];
        $this->_methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderConsumeNotification'),
            $this->equalTo([$rawBody])
        )->willReturn($response);
        $this->assertEquals($resultArray, $this->_model->consumeNotification($request));
    }

    public function testGetNewStatus()
    {
        $this->assertEquals(Order::STATUS_NEW, $this->_model->getNewStatus());
    }

    public function testPaymentSuccessCheckFail()
    {
        $this->_request->expects($this->once())->method('getParam')->with($this->equalTo('error'))->willReturn('501');
        $this->assertFalse($this->_model->paymentSuccessCheck());
    }

    public function testPaymentSuccessCheckSuccess()
    {
        $this->_request->expects($this->once())->method('getParam')->with($this->equalTo('error'))->willReturn(null);
        $this->assertTrue($this->_model->paymentSuccessCheck());
    }
    
    public function testCanProcessNotificationFailStatusCompleted()
    {
        $payuplOrderId = 'ABC';
        $this->_transactionResource->expects($this->once())->method('getStatusByPayuplOrderId')->with($payuplOrderId)->willReturn(Order::STATUS_COMPLETED);
        $this->assertFalse($this->_model->canProcessNotification($payuplOrderId));
    }

    public function testCanProcessNotificationFailStatusCancelled()
    {
        $payuplOrderId = 'ABC';
        $this->_transactionResource->expects($this->once())->method('getStatusByPayuplOrderId')->with($payuplOrderId)->willReturn(Order::STATUS_CANCELLED);
        $this->assertFalse($this->_model->canProcessNotification($payuplOrderId));
    }

    public function testCanProcessNotificationSuccess()
    {
        $payuplOrderId = 'ABC';
        $this->_transactionResource->expects($this->once())->method('getStatusByPayuplOrderId')->with($payuplOrderId)->willReturn('OTHER STATUS');
        $this->assertTrue($this->_model->canProcessNotification($payuplOrderId));
    }

    public function testProcessNotificationSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_COMPLETED;
        $amount = 2.22;
        $check = true;
        $this->_transactionResource->expects($this->once())->method('checkIfNewestByPayuplOrderId')->with($payuplOrderId)->willReturn($check);
        $this->_orderProcessor->expects($this->once())->method('processStatusChange')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo($amount),
            $this->equalTo($check)
        );
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)->setMethods(['setHttpResponseCode'])->disableOriginalConstructor()->getMock();
        $result->expects($this->once())->method('setHttpResponseCode')->with(200)->will($this->returnSelf());
        $this->_rawResultFactory->expects($this->once())->method('create')->willReturn($result);
        $this->assertEquals($result, $this->_model->processNotification($payuplOrderId, $status, $amount));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getRequestMock()
    {
        return $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->setMethods(['isPost', 'getContent'])->disableOriginalConstructor()->getMock();
    }
}