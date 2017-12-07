<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Transaction;

use Magento\Framework\Exception\LocalizedException;

class ServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Service
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResource;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->transactionRepository = $this->getMockBuilder(\Magento\Sales\Api\TransactionRepositoryInterface::class)
            ->getMock();
        $this->transactionResource = $this->getMockBuilder(\Orba\Payupl\Model\ResourceModel\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(Service::class, [
            'transactionRepository' => $this->transactionRepository,
            'transactionResource' => $this->transactionResource
        ]);
    }

    public function testUpdateStatusFailNotFound()
    {
        $payuplOrderId = 'ABC';
        $this->transactionResource->expects($this->once())->method('getIdByPayuplOrderId')
            ->with($this->equalTo($payuplOrderId))->willReturn(false);
        $this->expectException(LocalizedException::class, 'Transaction ' . $payuplOrderId . ' not found.');
        $this->model->updateStatus($payuplOrderId, 'COMPLETED');
    }

    public function testUpdateStatusSuccessDontClose()
    {
        $payuplOrderId = 'ABC';
        $status = 'COMPLETED';
        $transaction = $this->getTransactionMockForUpdateStatus($payuplOrderId, $status);
        $transaction->expects($this->once())->method('save')->will($this->returnSelf());
        $this->model->updateStatus($payuplOrderId, $status, false);
    }

    public function testUpdateStatusSuccessClose()
    {
        $payuplOrderId = 'ABC';
        $status = 'COMPLETED';
        $transaction = $this->getTransactionMockForUpdateStatus($payuplOrderId, $status);
        $transaction->expects($this->once())->method('setIsClosed')->with($this->equalTo(1))->will($this->returnSelf());
        $transaction->expects($this->once())->method('save')->will($this->returnSelf());
        $this->model->updateStatus($payuplOrderId, $status, true);
    }

    /**
     * @param $payuplOrderId
     * @param $status
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTransactionMockForUpdateStatus($payuplOrderId, $status)
    {
        $id = 1;
        $transaction = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)
            ->disableOriginalConstructor()->getMock();
        $this->transactionResource->expects($this->once())->method('getIdByPayuplOrderId')
            ->with($this->equalTo($payuplOrderId))->willReturn($id);
        $this->transactionRepository->expects($this->once())->method('get')->with($this->equalTo($id))
            ->willReturn($transaction);
        $rawDetailsInfo = [
            'status' => 'OLD',
            'other' => 'data'
        ];
        $transaction->expects($this->once())->method('getAdditionalInformation')
            ->with($this->equalTo(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS))
            ->willReturn($rawDetailsInfo);
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
