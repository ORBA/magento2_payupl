<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $refundHelper;

    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->configHelper = $this->getMockBuilder(Client\ConfigInterface::class)->disableOriginalConstructor()
            ->getMock();
        $this->orderHelper = $this->getMockBuilder(Client\OrderInterface::class)->disableOriginalConstructor()
            ->getMock();
        $this->refundHelper = $this->getMockBuilder(Client\RefundInterface::class)->disableOriginalConstructor()
            ->getMock();
        $this->model = $this->getModel();
    }

    public function testSetConfigInConstructor()
    {
        $this->configHelper->expects($this->once())->method('setConfig')->willReturn(true);
        // This will run constructor.
        $this->getModel();
    }

    public function testOrderCreateInvalidData()
    {
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'Order request data array is invalid.');
        $this->orderHelper->expects($this->once())->method('validateCreate')->willReturn(false);
        $this->model->orderCreate();
    }

    public function testOrderCreateFail()
    {
        $data = ['data'];
        $dataExtended = ['data_extended'];
        $this->setExpectedException(
            \Orba\Payupl\Model\Client\Exception::class,
            'There was a problem while processing order create request.'
        );
        $this->orderHelper->expects($this->once())->method('validateCreate')->willReturn(true);
        $this->orderHelper->expects($this->once())->method('addSpecialDataToOrder')->with($this->equalTo($data))
            ->willReturn($dataExtended);
        $this->orderHelper->expects($this->once())->method('create')->with($this->equalTo($dataExtended))
            ->willReturn(false);
        $this->model->orderCreate($data);
    }

    public function testOrderCreateSuccess()
    {
        $data = ['data'];
        $dataExtended = ['data_extended'];
        $result = $this->getResultMock();
        $this->orderHelper->expects($this->once())->method('validateCreate')->with($this->equalTo($data))
            ->willReturn(true);
        $this->orderHelper->expects($this->once())->method('addSpecialDataToOrder')->with($this->equalTo($data))
            ->willReturn($dataExtended);
        $this->orderHelper->expects($this->once())->method('create')->with($this->equalTo($dataExtended))
            ->willReturn($result);
        $this->assertEquals($result, $this->model->orderCreate($data));
    }
    
    public function testOrderRetrieveEmptyId()
    {
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'ID of order to retrieve is empty.');
        $this->orderHelper->expects($this->once())->method('validateRetrieve')->willReturn(false);
        $this->model->orderRetrieve('');
    }

    public function testOrderRetrieveFail()
    {
        $id = '123456';
        $this->setExpectedException(
            \Orba\Payupl\Model\Client\Exception::class,
            'There was a problem while processing order retrieve request.'
        );
        $this->orderHelper->expects($this->once())->method('validateRetrieve')->willReturn(true);
        $this->orderHelper->expects($this->once())->method('retrieve')->with($this->equalTo($id))->willReturn(false);
        $this->model->orderRetrieve($id);
    }

    public function testOrderRetrieveSuccess()
    {
        $id = '123456';
        $result = $this->getResultMock();
        $this->orderHelper->expects($this->once())->method('validateRetrieve')->with($this->equalTo($id))
            ->willReturn(true);
        $this->orderHelper->expects($this->once())->method('retrieve')->with($this->equalTo($id))->willReturn($result);
        $this->assertEquals($result, $this->model->orderRetrieve($id));
    }

    public function testOrderCancelEmptyId()
    {
        $this->setExpectedException(\Orba\Payupl\Model\Client\Exception::class, 'ID of order to cancel is empty.');
        $this->orderHelper->expects($this->once())->method('validateCancel')->willReturn(false);
        $this->model->orderCancel('');
    }

    public function testOrderCancelFail()
    {
        $id = '123456';
        $this->setExpectedException(
            \Orba\Payupl\Model\Client\Exception::class,
            'There was a problem while processing order cancel request.'
        );
        $this->orderHelper->expects($this->once())->method('validateCancel')->willReturn(true);
        $this->orderHelper->expects($this->once())->method('cancel')->with($this->equalTo($id))->willReturn(false);
        $this->model->orderCancel($id);
    }

    public function testOrderCancelSuccess()
    {
        $id = '123456';
        $result = $this->getResultMock();
        $this->orderHelper->expects($this->once())->method('validateCancel')->with($this->equalTo($id))
            ->willReturn(true);
        $this->orderHelper->expects($this->once())->method('cancel')->with($this->equalTo($id))->willReturn($result);
        $this->assertEquals($result, $this->model->orderCancel($id));
    }

    public function testOrderStatusUpdateInvalidData()
    {
        $this->setExpectedException(
            \Orba\Payupl\Model\Client\Exception::class,
            'Order status update request data array is invalid.'
        );
        $this->orderHelper->expects($this->once())->method('validateStatusUpdate')->willReturn(false);
        $this->model->orderStatusUpdate();
    }

    public function testOrderStatusUpdateFail()
    {
        $data = ['data'];
        $this->setExpectedException(
            \Orba\Payupl\Model\Client\Exception::class,
            'There was a problem while processing order status update request.'
        );
        $this->orderHelper->expects($this->once())->method('validateStatusUpdate')->willReturn(true);
        $this->orderHelper->expects($this->once())->method('statusUpdate')->with($this->equalTo($data))
            ->willReturn(false);
        $this->model->orderStatusUpdate($data);
    }

    public function testOrderStatusUpdateSuccess()
    {
        $data = ['data'];
        $result = $this->getResultMock();
        $this->orderHelper->expects($this->once())->method('validateStatusUpdate')->with($this->equalTo($data))
            ->willReturn(true);
        $this->orderHelper->expects($this->once())->method('statusUpdate')->with($this->equalTo($data))
            ->willReturn($result);
        $this->assertTrue($this->model->orderStatusUpdate($data));
    }

    public function testOrderConsumeNotificationFail()
    {
        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()
            ->getMock();
        $this->setExpectedException(
            \Orba\Payupl\Model\Client\Exception::class,
            'There was a problem while consuming order notification.'
        );
        $this->orderHelper->expects($this->once())->method('consumeNotification')->with($this->equalTo($request))
            ->willReturn(false);
        $this->model->orderConsumeNotification($request);
    }

    public function testOrderConsumeNotificationSuccess()
    {
        $request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()
            ->getMock();
        $result = $this->getResultMock();
        $this->orderHelper->expects($this->once())->method('consumeNotification')->with($this->equalTo($request))
            ->willReturn($result);
        $this->assertEquals($result, $this->model->orderConsumeNotification($request));
    }

    public function testRefundCreateInvalidData()
    {
        $this->setExpectedException(
            \Orba\Payupl\Model\Client\Exception::class,
            'Refund create request data is invalid.'
        );
        $this->refundHelper->expects($this->once())->method('validateCreate')->willReturn(false);
        $this->model->refundCreate();
    }

    public function testRefundCreateFail()
    {
        $this->setExpectedException(
            \Orba\Payupl\Model\Client\Exception::class,
            'There was a problem while processing refund create request.'
        );
        $this->refundHelper->expects($this->once())->method('validateCreate')->willReturn(true);
        $this->refundHelper->expects($this->once())->method('create')->willReturn(false);
        $this->model->refundCreate();
    }

    public function testRefundCreateSuccess()
    {
        $orderId = '123456';
        $description = 'Description';
        $amount = '100';
        $result = $this->getResultMock();
        $this->refundHelper->expects($this->once())->method('validateCreate')->with(
            $this->equalTo($orderId),
            $this->equalTo($description),
            $this->equalTo($amount)
        )->willReturn(true);
        $this->refundHelper->expects($this->once())->method('create')->with(
            $this->equalTo($orderId),
            $this->equalTo($description),
            $this->equalTo($amount)
        )->willReturn($result);
        $this->assertTrue($this->model->refundCreate($orderId, $description, $amount));
    }

    public function testGetPaytypes()
    {
        $paytypes = ['paytypes'];
        $this->orderHelper->expects($this->once())->method('getPaytypes')->willReturn($paytypes);
        $this->assertEquals($paytypes, $this->model->getPaytypes());
    }

    public function testGetOrderHelper()
    {
        $this->assertInstanceOf(Client\OrderInterface::class, $this->model->getOrderHelper());
    }

    /**
     * @return object
     */
    protected function getModel()
    {
        return $this->objectManagerHelper->getObject(
            Client::class,
            [
                'configHelper' => $this->configHelper,
                'orderHelper' => $this->orderHelper,
                'refundHelper' => $this->refundHelper
            ]
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResultMock()
    {
        return 'result';
    }
}
