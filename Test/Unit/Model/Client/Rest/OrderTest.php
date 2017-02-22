<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Orba\Payupl\Model\Client\Rest\Order;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Order
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataGetter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodCaller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rawResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->dataValidator = $this->getMockBuilder(Order\DataValidator::class)->getMock();
        $this->dataGetter = $this->getMockBuilder(Order\DataGetter::class)->disableOriginalConstructor()->getMock();
        $this->methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderProcessor = $this->getMockBuilder(Order\Processor::class)->disableOriginalConstructor()->getMock();
        $this->rawResultFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();
        $this->model = $objectManagerHelper->getObject(
            Order::class,
            [
                'dataValidator' => $this->dataValidator,
                'dataGetter' => $this->dataGetter,
                'methodCaller' => $this->methodCaller,
                'transactionResource' => $this->transactionResource,
                'orderProcessor' => $this->orderProcessor,
                'rawResultFactory' => $this->rawResultFactory,
                'request' => $this->request
            ]
        );
    }

    public function testValidateCreateFailedEmpty()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->model->validateCreate());
    }

    public function testValidateCreateFailedInvalidBasicData()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->dataValidator->expects($this->once())->method('validateBasicData')->willReturn(false);
        $this->assertFalse($this->model->validateCreate());
    }

    public function testValidateCreateFailedInvalidProductsData()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->dataValidator->expects($this->once())->method('validateBasicData')->willReturn(true);
        $this->dataValidator->expects($this->once())->method('validateProductsData')->willReturn(false);
        $this->assertFalse($this->model->validateCreate());
    }

    public function testValidateCreateSuccess()
    {
        $data = ['data'];
        $this->dataValidator->expects($this->once())->method('validateEmpty')->with($this->equalTo($data))
            ->willReturn(true);
        $this->dataValidator->expects($this->once())->method('validateBasicData')->with($this->equalTo($data))
            ->willReturn(true);
        $this->dataValidator->expects($this->once())->method('validateProductsData')->with($this->equalTo($data))
            ->willReturn(true);
        $this->assertTrue($this->model->validateCreate($data));
    }

    public function testDataAdder()
    {
        $data = [
            'example' => true
        ];
        $this->dataGetter->expects($this->once())->method('getContinueUrl');
        $this->dataGetter->expects($this->once())->method('getNotifyUrl');
        $this->dataGetter->expects($this->once())->method('getCustomerIp');
        $this->dataGetter->expects($this->once())->method('getMerchantPosId');
        $extendedData = $this->model->addSpecialDataToOrder($data);
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
        $order = $this->getOrderMock();
        $this->preTestGetDataForOrderCreateSuccess($order, $productsData, $shippingData, $buyerData, $basicData);
        $productsData[] = $shippingData;
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData]
            ),
            $this->model->getDataForOrderCreate($order)
        );
    }

    public function testGetDataForOrderCreateSuccessNoShipping()
    {
        $productsData = ['products'];
        $shippingData = null;
        $buyerData = ['buyer'];
        $basicData = ['basic'];
        $order = $this->getOrderMock();
        $this->preTestGetDataForOrderCreateSuccess($order, $productsData, $shippingData, $buyerData, $basicData);
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData],
                ['buyer' => $buyerData]
            ),
            $this->model->getDataForOrderCreate($order)
        );
    }

    public function testGetDataForOrderCreateSuccessAllData()
    {
        $productsData = ['products'];
        $shippingData = ['shipping'];
        $buyerData = ['buyer'];
        $basicData = ['basic'];
        $order = $this->getOrderMock();
        $this->preTestGetDataForOrderCreateSuccess($order, $productsData, $shippingData, $buyerData, $basicData);
        $productsData[] = $shippingData;
        $this->assertEquals(
            array_merge(
                $basicData,
                ['products' => $productsData],
                ['buyer' => $buyerData]
            ),
            $this->model->getDataForOrderCreate($order)
        );
    }

    public function testCreateFail()
    {
        $data = ['data'];
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCreate'),
            $this->equalTo([$data])
        )->willReturn(false);
        $this->assertFalse($this->model->create($data));
    }

    public function testCreateSuccess()
    {
        $data = ['extOrderId' => '123'];
        $result = $this->getResultMock();
        $response = new \stdClass();
        $response->orderId = '456';
        $response->redirectUri = 'http://redirect.uri';
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCreate'),
            $this->equalTo([$data])
        )->willReturn($response);
        $returnArray = [
            'orderId' => $response->orderId,
            'redirectUri' => $response->redirectUri,
            'extOrderId' => $data['extOrderId']
        ];
        $this->assertEquals($returnArray, $this->model->create($data));
    }

    public function testValidateRetrieveFailedEmpty()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->model->validateRetrieve(''));
    }

    public function testValidateRetrieveSuccess()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->assertTrue($this->model->validateRetrieve('ABC'));
    }

    public function testRetrieveFail()
    {
        $id = '123456';
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderRetrieve'),
            $this->equalTo([$id])
        )->willReturn(false);
        $this->assertFalse($this->model->retrieve($id));
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
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderRetrieve'),
            $this->equalTo([$payuplOrderId])
        )->willReturn($response);
        $result = [
            'status' => $response->orders[0]->status,
            'amount' => $response->orders[0]->totalAmount / 100
        ];
        $this->assertEquals($result, $this->model->retrieve($payuplOrderId));
    }

    public function testValidateCancelFailedEmpty()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->model->validateCancel(''));
    }

    public function testValidateCancelSuccess()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->assertTrue($this->model->validateCancel('ABC'));
    }

    public function testCancelFail()
    {
        $id = '123456';
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCancel'),
            $this->equalTo([$id])
        )->willReturn(false);
        $this->assertFalse($this->model->cancel($id));
    }

    public function testCancelSuccess()
    {
        $id = '123456';
        $result = $this->getResultMock();
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderCancel'),
            $this->equalTo([$id])
        )->willReturn($result);
        $this->assertTrue($this->model->cancel($id));
    }

    public function testValidateStatusUpdateFailedEmpty()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(false);
        $this->assertFalse($this->model->validateStatusUpdate());
    }

    public function testValidateStatusUpdateInvalidData()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->dataValidator->expects($this->once())->method('validateStatusUpdateData')->willReturn(false);
        $this->assertFalse($this->model->validateStatusUpdate());
    }

    public function testValidateStatusUpdateSuccess()
    {
        $this->dataValidator->expects($this->once())->method('validateEmpty')->willReturn(true);
        $this->dataValidator->expects($this->once())->method('validateStatusUpdateData')->willReturn(true);
        $this->assertTrue($this->model->validateStatusUpdate(['data']));
    }

    public function testStatusUpdateFail()
    {
        $data = ['data'];
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderStatusUpdate'),
            $this->equalTo([$data])
        )->willReturn(false);
        $this->assertFalse($this->model->statusUpdate($data));
    }

    public function testStatusUpdateSuccess()
    {
        $data = ['data'];
        $result = $this->getResultMock();
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderStatusUpdate'),
            $this->equalTo([$data])
        )->willReturn($result);
        $this->assertTrue($this->model->statusUpdate($data));
    }

    public function testConsumeNotificationFailNoPost()
    {
        $request = $this->getRequestMock();
        $request->expects($this->once())->method('isPost')->willReturn(false);
        $this->setExpectedException(LocalizedException::class, 'POST request is required.');
        $this->model->consumeNotification($request);
    }

    public function testConsumeNotificationFail()
    {
        $rawBody = 'body';
        $request = $this->getRequestMock();
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->once())->method('getContent')->willReturn($rawBody);
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderConsumeNotification'),
            $this->equalTo([$rawBody])
        )->willReturn(false);
        $this->assertFalse($this->model->consumeNotification($request));
    }

    public function testConsumeNotificationSuccess()
    {
        $rawBody = 'body';
        $request = $this->getRequestMock();
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
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderConsumeNotification'),
            $this->equalTo([$rawBody])
        )->willReturn($response);
        $this->assertEquals($resultArray, $this->model->consumeNotification($request));
    }

    public function testGetNewStatus()
    {
        $this->assertEquals(Order::STATUS_NEW, $this->model->getNewStatus());
    }

    public function testPaymentSuccessCheckFail()
    {
        $this->request->expects($this->once())->method('getParam')->with($this->equalTo('error'))->willReturn('501');
        $this->assertFalse($this->model->paymentSuccessCheck());
    }

    public function testPaymentSuccessCheckSuccess()
    {
        $this->request->expects($this->once())->method('getParam')->with($this->equalTo('error'))->willReturn(null);
        $this->assertTrue($this->model->paymentSuccessCheck());
    }
    
    public function testCanProcessNotificationFailStatusCompleted()
    {
        $payuplOrderId = 'ABC';
        $this->transactionResource->expects($this->once())->method('getStatusByPayuplOrderId')->with($payuplOrderId)
            ->willReturn(Order::STATUS_COMPLETED);
        $this->assertFalse($this->model->canProcessNotification($payuplOrderId));
    }

    public function testCanProcessNotificationFailStatusCancelled()
    {
        $payuplOrderId = 'ABC';
        $this->transactionResource->expects($this->once())->method('getStatusByPayuplOrderId')->with($payuplOrderId)
            ->willReturn(Order::STATUS_CANCELLED);
        $this->assertFalse($this->model->canProcessNotification($payuplOrderId));
    }

    public function testCanProcessNotificationFailStatusEqualFalse()
    {
        $payuplOrderId = 'ABC';
        $this->transactionResource->expects($this->once())->method('getStatusByPayuplOrderId')->with($payuplOrderId)
            ->willReturn(false);
        $this->assertFalse($this->model->canProcessNotification($payuplOrderId));
    }

    public function testCanProcessNotificationSuccess()
    {
        $payuplOrderId = 'ABC';
        $this->transactionResource->expects($this->once())->method('getStatusByPayuplOrderId')->with($payuplOrderId)
            ->willReturn('OTHER STATUS');
        $this->assertTrue($this->model->canProcessNotification($payuplOrderId));
    }

    public function testProcessNotificationSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = Order::STATUS_COMPLETED;
        $amount = 2.22;
        $check = true;
        $this->transactionResource->expects($this->once())->method('checkIfNewestByPayuplOrderId')->with($payuplOrderId)
            ->willReturn($check);
        $this->orderProcessor->expects($this->once())->method('processStatusChange')->with(
            $this->equalTo($payuplOrderId),
            $this->equalTo($status),
            $this->equalTo($amount),
            $this->equalTo($check)
        );
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->setMethods(['setHttpResponseCode'])->disableOriginalConstructor()->getMock();
        $result->expects($this->once())->method('setHttpResponseCode')->with(200)->will($this->returnSelf());
        $this->rawResultFactory->expects($this->once())->method('create')->willReturn($result);
        $this->assertEquals($result, $this->model->processNotification($payuplOrderId, $status, $amount));
    }

    public function testGetPaytypes()
    {
        $this->assertFalse($this->model->getPaytypes());
    }

    public function testGetStatusDescription()
    {
        $this->assertFalse($this->model->getStatusDescription('invalid'));
        $statusDescription = $this->model->getStatusDescription(Order::STATUS_NEW);
        $this->assertInternalType('string', $statusDescription);
        $this->assertNotEmpty($statusDescription);
    }

    public function testGetAllStatuses()
    {
        $this->assertInternalType('array', $this->model->getAllStatuses());
        $this->assertNotEmpty($this->model->getAllStatuses());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResultMock()
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
    protected function preTestGetDataForOrderCreateSuccess(
        \Magento\Sales\Model\Order $order,
        array $productsData,
        $shippingData,
        $buyerData,
        array $basicData
    ) {
        $this->dataGetter->expects($this->once())->method('getProductsData')->with($this->equalTo($order))
            ->willReturn($productsData);
        $this->dataGetter->expects($this->once())->method('getShippingData')->with($this->equalTo($order))
            ->willReturn($shippingData);
        $this->dataGetter->expects($this->once())->method('getBuyerData')->with($this->equalTo($order))
            ->willReturn($buyerData);
        $this->dataGetter->expects($this->once())->method('getBasicData')->with($this->equalTo($order))
            ->willReturn($basicData);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock()
    {
        return $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestMock()
    {
        return $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->setMethods(['isPost', 'getContent'])
            ->disableOriginalConstructor()->getMock();
    }
}
