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
    protected $_transactionRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_transactionResource;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_transactionRepository = $this->getMockBuilder(\Magento\Sales\Api\TransactionRepositoryInterface::class)->getMock();
        $this->_transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Service::class, [
            'transactionRepository' => $this->_transactionRepository,
            'transactionResource' => $this->_transactionResource
        ]);
    }

    public function testUpdateStatusFailNotFound()
    {
        $payuplOrderId = 'ABC';
        $this->_transactionResource->expects($this->once())->method('getIdByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn(false);
        $this->setExpectedException(Exception::class, 'Transaction ' . $payuplOrderId . ' not found.');
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
        $id = 1;
        $transaction = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)->disableOriginalConstructor()->getMock();
        $this->_transactionResource->expects($this->once())->method('getIdByPayuplOrderId')->with($this->equalTo($payuplOrderId))->willReturn($id);
        $this->_transactionRepository->expects($this->once())->method('get')->with($this->equalTo($id))->willReturn($transaction);
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