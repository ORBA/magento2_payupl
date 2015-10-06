<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Transaction;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Service
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderHelper;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_transactionFactory = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\TransactionFactory::class)->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->_orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Service::class, [
            'transactionFactory' => $this->_transactionFactory,
            'orderHelper' => $this->_orderHelper
        ]);
    }

    public function testUpdateStatusFailNotFound()
    {
        $payuplOrderId = 'ABC';
        $this->_orderHelper->expects($this->once())->method('loadOrderByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn(false);
        $this->setExpectedException(Exception::class, 'Transaction not found.');
        $this->_model->updateStatus($payuplOrderId, 'COMPLETED');
    }

    public function testUpdateStatusSuccessDontClose()
    {
        $payuplOrderId = 'ABC';
        $status = 'COMPLETED';
        $transaction = $this->_getTransactionMockForUpdateStatus($payuplOrderId, $status);
        $transaction->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_model->updateStatus($payuplOrderId, $status, false);
    }

    public function testUpdateStatusSuccessClose()
    {
        $payuplOrderId = 'ABC';
        $status = 'COMPLETED';
        $transaction = $this->_getTransactionMockForUpdateStatus($payuplOrderId, $status);
        $transaction->expects($this->once())->method('setIsClosed')->with($this->equalTo(1))->will($this->returnSelf());
        $transaction->expects($this->once())->method('save')->will($this->returnSelf());
        $this->_model->updateStatus($payuplOrderId, $status, true);
    }

    /**
     * @param $payuplOrderId
     * @param $status
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTransactionMockForUpdateStatus($payuplOrderId, $status)
    {
        $transaction = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)->disableOriginalConstructor()->getMock();
        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $payment->expects($this->once())->method('getTransaction')->with($this->equalTo($payuplOrderId))->willReturn($transaction);
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getPayment')->willReturn($payment);
        $this->_orderHelper->expects($this->once())->method('loadOrderByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn($order);
        $rawDetailsInfo = [
            'status' => 'OLD',
            'other' => 'data'
        ];
        $transaction->expects($this->once())->method('getAdditionalInformation')->with($this->equalTo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS))->willReturn($rawDetailsInfo);
        $transaction->expects($this->once())->method('setAdditionalInformation')->with(
            $this->equalTo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS),
            $this->equalTo([
                'status' => $status,
                'other' => 'data'
            ])
        )->will($this->returnSelf());
        return $transaction;
    }
}