<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
    protected $urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $notificationHelper;

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
    protected $orderProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rawResultFactory;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->dataValidator = $this->getMockBuilder(Order\DataValidator::class)->getMock();
        $this->dataGetter = $this->getMockBuilder(Order\DataGetter::class)->disableOriginalConstructor()->getMock();
        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $this->session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['setOrderCreateData'])
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()
            ->getMock();
        $this->notificationHelper = $this->getMockBuilder(Order\Notification::class)->disableOriginalConstructor()
            ->getMock();
        $this->methodCaller = $this->getMockBuilder(MethodCaller::class)->disableOriginalConstructor()->getMock();
        $this->transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->orderProcessor = $this->getMockBuilder(Order\Processor::class)->disableOriginalConstructor()->getMock();
        $this->rawResultFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();

        $this->model = $objectManagerHelper->getObject(
            Order::class,
            [
                'urlInterface' => $this->urlBuilder,
                'dataValidator' => $this->dataValidator,
                'dataGetter' => $this->dataGetter,
                'session' => $this->session,
                'request' => $this->request,
                'logger' => $this->logger,
                'notificationHelper' => $this->notificationHelper,
                'methodCaller' => $this->methodCaller,
                'transactionResource' => $this->transactionResource,
                'orderProcessor' => $this->orderProcessor,
                'rawResultFactory' => $this->rawResultFactory
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

    public function testValidateCreateSuccess()
    {
        $data = ['data'];
        $this->dataValidator->expects($this->once())->method('validateEmpty')->with($this->equalTo($data))
            ->willReturn(true);
        $this->dataValidator->expects($this->once())->method('validateBasicData')->with($this->equalTo($data))
            ->willReturn(true);
        $this->assertTrue($this->model->validateCreate($data));
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

    public function testGetDataForOrderCreate()
    {
        $basicData = ['basic'];
        $order = $this->getOrderMock();
        $this->dataGetter->expects($this->once())->method('getBasicData')->with($this->equalTo($order))
            ->willReturn($basicData);
        $this->assertEquals($basicData, $this->model->getDataForOrderCreate($order));
    }

    public function testDataAdder()
    {
        $data = [
            'example' => true
        ];
        $posId = '123456';
        $posAuthKey = 'ABC';
        $clientIp = '123.123.123.123';
        $ts = '12345678';
        $sig = 'abcdfeghij123';
        $extendedDataBeforeSig = array_merge($data, [
            'pos_id' => $posId,
            'pos_auth_key' => $posAuthKey,
            'client_ip' => $clientIp,
            'ts' => $ts
        ]);
        $this->dataGetter->expects($this->once())->method('getPosId')->willReturn($posId);
        $this->dataGetter->expects($this->once())->method('getPosAuthKey')->willReturn($posAuthKey);
        $this->dataGetter->expects($this->once())->method('getClientIp')->willReturn($clientIp);
        $this->dataGetter->expects($this->once())->method('getTs')->willReturn($ts);
        $this->dataGetter->expects($this->once())->method('getSigForOrderCreate')
            ->with($this->equalTo($extendedDataBeforeSig))->willReturn($sig);
        $this->assertEquals(
            array_merge($extendedDataBeforeSig, ['sig' => $sig]),
            $this->model->addSpecialDataToOrder($data)
        );
    }

    public function testGetNewStatus()
    {
        $this->assertEquals(Order::STATUS_PRE_NEW, $this->model->getNewStatus());
    }

    public function testCreate()
    {
        $data = [
            'session_id' => 'ABC',
            'order_id' => '000000012'
        ];
        $path = 'orba_payupl/classic/form';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $result = [
            'orderId' => $data['session_id'],
            'extOrderId' => $data['order_id'],
            'redirectUri' => $url
        ];
        $this->urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->session->expects($this->once())->method('setOrderCreateData')->with($this->equalTo($data));
        $this->assertEquals($result, $this->model->create($data));
    }

    public function testPaymentSuccessCheckFail()
    {
        $errorCode = '100';
        $extOrderId = 'ABC';
        $this->request->expects($this->at(0))->method('getParam')->with($this->equalTo('error'))
            ->willReturn($errorCode);
        $this->request->expects($this->at(1))->method('getParam')->with($this->equalTo('session_id'))
            ->willReturn($extOrderId);
        $this->logger->expects($this->once())->method('error')
            ->with('Payment error ' . $errorCode . ' for transaction ' . $extOrderId . '.');
        $this->assertFalse($this->model->paymentSuccessCheck());
    }

    public function testPaymentSuccessCheckSuccess()
    {
        $this->request->expects($this->once())->method('getParam')->with($this->equalTo('error'))->willReturn(null);
        $this->assertTrue($this->model->paymentSuccessCheck());
    }

    public function testRetrieveFail()
    {
        $payuplOrderId = 'ABC';
        $this->setExpectationsForOrderRetrieve(123456, 345678, $payuplOrderId, 'DEF', false);
        $this->assertFalse($this->model->retrieve($payuplOrderId));
    }

    public function testRetrieveSuccess()
    {
        $payuplOrderId = 'ABC';
        $response = (object) [
            'transStatus' => Order::STATUS_COMPLETED,
            'transAmount' => '3200'
        ];
        $this->setExpectationsForOrderRetrieve(123456, 345678, $payuplOrderId, 'DEF', $response);
        $result = [
            'status' => $response->transStatus,
            'amount' => $response->transAmount / 100
        ];
        $this->assertEquals($result, $this->model->retrieve($payuplOrderId));
    }

    public function testConsumeNotificationFailRequestException()
    {
        $request = $this->getRequestMock();
        $message = 'Exception message';
        $this->notificationHelper->expects($this->once())->method('getPayuplOrderId')->with($this->equalTo($request))
            ->willThrowException(new LocalizedException(new Phrase($message)));
        $this->setExpectedException(LocalizedException::class, $message);
        $this->model->consumeNotification($request);
    }

    public function testConsumeNotificationFailRetrieve()
    {
        $payuplOrderId = 'ABC';
        $request = $this->getRequestMock();
        $this->notificationHelper->expects($this->once())->method('getPayuplOrderId')->with($this->equalTo($request))
            ->willReturn($payuplOrderId);
        $this->setExpectationsForOrderRetrieve(123456, 345678, $payuplOrderId, 'DEF', false);
        $this->assertFalse($this->model->consumeNotification($request));
    }

    public function testConsumeNotificationSuccess()
    {
        $payuplOrderId = 'ABC';
        $request = $this->getRequestMock();
        $response = (object) [
            'transStatus' => Order::STATUS_COMPLETED,
            'transAmount' => '3200'
        ];
        $this->notificationHelper->expects($this->once())->method('getPayuplOrderId')->with($this->equalTo($request))
            ->willReturn($payuplOrderId);
        $this->setExpectationsForOrderRetrieve(123456, 345678, $payuplOrderId, 'DEF', $response);
        $result = [
            'payuplOrderId' => $payuplOrderId,
            'status' => $response->transStatus,
            'amount' => $response->transAmount / 100
        ];
        $this->assertEquals($result, $this->model->consumeNotification($request));
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
            ->setMethods(['setHttpResponseCode', 'setContents'])->disableOriginalConstructor()->getMock();
        $result->expects($this->once())->method('setHttpResponseCode')->with($this->equalTo(200))
            ->will($this->returnSelf());
        $result->expects($this->once())->method('setContents')->with($this->equalTo('OK'))->will($this->returnSelf());
        $this->rawResultFactory->expects($this->once())->method('create')->willReturn($result);
        $this->assertEquals($result, $this->model->processNotification($payuplOrderId, $status, $amount));
    }
    
    public function testGetPaytypes()
    {
        $response = ['paytypes'];
        $this->methodCaller->expects($this->once())->method('call')->with($this->equalTo('getPaytypes'))
            ->willReturn($response);
        $this->assertEquals($response, $this->model->getPaytypes());
    }

    public function testGetStatusDescription()
    {
        $this->assertFalse($this->model->getStatusDescription('invalid'));
        $statusDescription = $this->model->getStatusDescription(0);
        $this->assertInternalType('string', $statusDescription);
        $this->assertNotEmpty($statusDescription);
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
        return $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $posId
     * @param $ts
     * @param $payuplOrderId
     * @param $sig
     * @param $response
     */
    protected function setExpectationsForOrderRetrieve($posId, $ts, $payuplOrderId, $sig, $response)
    {
        $this->dataGetter->expects($this->once())->method('getPosId')->willReturn($posId);
        $this->dataGetter->expects($this->once())->method('getTs')->willReturn($ts);
        $this->dataGetter->expects($this->once())->method('getSigForOrderRetrieve')->with($this->equalTo([
            'pos_id' => $posId,
            'session_id' => $payuplOrderId,
            'ts' => $ts
        ]))->willReturn($sig);
        $this->methodCaller->expects($this->once())->method('call')->with(
            $this->equalTo('orderRetrieve'),
            $this->equalTo([
                $posId,
                $payuplOrderId,
                $ts,
                $sig
            ])
        )->willReturn($response);
    }
}
