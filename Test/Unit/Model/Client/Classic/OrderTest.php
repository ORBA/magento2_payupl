<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

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
    protected $_urlBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dataValidator = $this->getMockBuilder(Order\DataValidator::class)->getMock();
        $this->_dataGetter = $this->getMockBuilder(Order\DataGetter::class)->disableOriginalConstructor()->getMock();
        $this->_urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $this->_session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)->setMethods(['setOrderCreateData'])->disableOriginalConstructor()->getMock();
        $this->_request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $this->_logger = $this->getMockBuilder(\Orba\Payupl\Logger\Logger::class)->disableOriginalConstructor()->getMock();
        $context = $objectManagerHelper->getObject(
            \Magento\Framework\View\Context::class,
            ['urlBuilder' => $this->_urlBuilder]
        );
        $this->_model = $objectManagerHelper->getObject(
            Order::class,
            [
                'context' => $context,
                'dataValidator' => $this->_dataValidator,
                'dataGetter' => $this->_dataGetter,
                'session' => $this->_session,
                'request' => $this->_request,
                'logger' => $this->_logger
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

    public function testValidateCreateSuccess()
    {
        $data = ['data'];
        $this->_dataValidator->expects($this->once())->method('validateEmpty')->with($this->equalTo($data))->willReturn(true);
        $this->_dataValidator->expects($this->once())->method('validateBasicData')->with($this->equalTo($data))->willReturn(true);
        $this->assertTrue($this->_model->validateCreate($data));
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

    public function testGetDataForOrderCreate()
    {
        $basicData = ['basic'];
        $order = $this->_getOrderMock();
        $this->_dataGetter->expects($this->once())->method('getBasicData')->with($this->equalTo($order))->willReturn($basicData);
        $this->assertEquals($basicData, $this->_model->getDataForOrderCreate($order));
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
        $this->_dataGetter->expects($this->once())->method('getPosId')->willReturn($posId);
        $this->_dataGetter->expects($this->once())->method('getPosAuthKey')->willReturn($posAuthKey);
        $this->_dataGetter->expects($this->once())->method('getClientIp')->willReturn($clientIp);
        $this->_dataGetter->expects($this->once())->method('getTs')->willReturn($ts);
        $this->_dataGetter->expects($this->once())->method('getSigForOrderCreate')->with($this->equalTo($extendedDataBeforeSig))->willReturn($sig);
        $this->assertEquals(array_merge($extendedDataBeforeSig, ['sig' => $sig]), $this->_model->addSpecialDataToOrder($data));
    }

    public function testGetNewStatus()
    {
        $this->assertEquals(Order::STATUS_PRE_NEW, $this->_model->getNewStatus());
    }

    public function testCreate()
    {
        $data = [
            'session_id' => 'ABC'
        ];
        $path = 'orba_payupl/classic/form';
        $baseUrl = 'http://example.com/';
        $url = $baseUrl . $path;
        $result = [
            'orderId' => md5($data['session_id']),
            'extOrderId' => $data['session_id'],
            'redirectUri' => $url
        ];
        $this->_urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));
        $this->_session->expects($this->once())->method('setOrderCreateData')->with($this->equalTo($data));
        $this->assertEquals($result, $this->_model->create($data));
    }

    public function testPaymentSuccessCheckFail()
    {
        $errorCode = '100';
        $extOrderId = 'ABC';
        $this->_request->expects($this->at(0))->method('getParam')->with($this->equalTo('error'))->willReturn($errorCode);
        $this->_request->expects($this->at(1))->method('getParam')->with($this->equalTo('session_id'))->willReturn($extOrderId);
        $this->_logger->expects($this->once())->method('error')->with('Payment error ' . $errorCode . ' for transaction ' . $extOrderId . '.');
        $this->assertFalse($this->_model->paymentSuccessCheck());
    }

    public function testPaymentSuccessCheckSuccess()
    {
        $this->_request->expects($this->once())->method('getParam')->with($this->equalTo('error'))->willReturn(null);
        $this->assertTrue($this->_model->paymentSuccessCheck());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock()
    {
        return $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
    }
}