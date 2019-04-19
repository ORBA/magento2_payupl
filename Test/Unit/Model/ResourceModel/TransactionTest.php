<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\SerializerInterface;
use Orba\Payupl\Test\Util;

class TransactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Transaction
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $date;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->date = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)->disableOriginalConstructor()
            ->getMock();
        $this->resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();
        $this->adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->resource->expects($this->any())->method('getConnection')->willReturn($this->adapter);
        $context = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResources')->willReturn($this->resource);
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)->getMockForAbstractClass();
        $this->model = $objectManager->getObject(Transaction::class, [
            'context' => $context,
            'date' => $this->date,
            'serializer' => $this->serializer
        ]);
    }
    
    public function testGetLastPayuplOrderIdByOrderIdFail()
    {
        $orderId = 1;
        $resultTableRow = null;
        $this->internalTestGetLastPayuplOrderIdByOrderId($orderId, $resultTableRow);
        $this->assertFalse($this->model->getLastPayuplOrderIdByOrderId($orderId));
    }

    public function testGetLastPayuplOrderIdByOrderIdSuccess()
    {
        $orderId = 1;
        $resultTableRow = [
            'txn_id' => 'ABC'
        ];
        $this->internalTestGetLastPayuplOrderIdByOrderId($orderId, $resultTableRow);
        $this->assertEquals($resultTableRow['txn_id'], $this->model->getLastPayuplOrderIdByOrderId($orderId));
    }

    public function testCheckIfNewestByPayuplOrderIdFailNotFound()
    {
        $payuplOrderId = '123';
        $resultTableRow = null;
        $this->internalTestCheckIfNewestByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testCheckIfNewestByPayuplOrderIdFailOld()
    {
        $payuplOrderId = '123';
        $resultTableRow = [
            'newer_id' => 2
        ];
        $this->internalTestCheckIfNewestByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testCheckIfNewestByPayuplOrderIdSuccess()
    {
        $payuplOrderId = '123';
        $resultTableRow = [
            'newer_id' => null
        ];
        $this->internalTestCheckIfNewestByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertTrue($this->model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testGetOrderIdByPayuplOrderIdFail()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = null;
        $this->internalTestGetOrderIdByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->model->getOrderIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetOrderIdByPayuplOrderIdSuccess()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = [
            'order_id' => 1
        ];
        $this->internalTestGetOrderIdByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertEquals($resultTableRow['order_id'], $this->model->getOrderIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetStatusByPayuplOrderIdFail()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = null;
        $this->internalTestGetAdditionalDataByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->model->getStatusByPayuplOrderId($payuplOrderId));
    }

    public function testGetStatusByPayuplOrderIdSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'PENDING';
        $resultTableRow = ['additional_information' => 'serialized data'];
        $this->serializer->expects($this->once())->method('unserialize')->with('serialized data')
            ->willReturn([
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [
                    'status' => $status
                ]
            ]);
        $this->internalTestGetAdditionalDataByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertEquals($status, $this->model->getStatusByPayuplOrderId($payuplOrderId));
    }

    public function testGetLastTryByOrderIdFirst()
    {
        $orderId = 1;
        $resultTableRow = null;
        $this->internalTestGetLastByOrderId($orderId, $resultTableRow);
        $this->assertEquals(0, $this->model->getLastTryByOrderId($orderId));
    }

    public function testGetLastTryByOrderIdNotFirst()
    {
        $orderId = 1;
        $try = 2;
        $resultTableRow = ['additional_information' => 'serialized data'];
        $this->serializer->expects($this->once())->method('unserialize')->with('serialized data')
            ->willReturn([
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [
                    'try' => $try
                ]
            ]);
        $this->internalTestGetLastByOrderId($orderId, $resultTableRow);
        $this->assertEquals($try, $this->model->getLastTryByOrderId($orderId));
    }

    public function testGetExtOrderIdByPayuplOrderIdFail()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = null;
        $this->internalTestGetAdditionalDataByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->model->getExtOrderIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetExtOrderIdByPayuplOrderIdSuccess()
    {
        $payuplOrderId = 'ABC';
        $extOrderId = '123';
        $resultTableRow = ['additional_information' => 'serialized data'];
        $this->serializer->expects($this->once())->method('unserialize')->with('serialized data')
            ->willReturn([
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [
                    'order_id' => $extOrderId
                ]
            ]);
        $this->internalTestGetAdditionalDataByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertEquals($extOrderId, $this->model->getExtOrderIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetIdByPayuplOrderIdFail()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = null;
        $this->internalTestGetIdByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->model->getIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetIdByPayuplOrderIdSuccess()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = [
            'transaction_id' => 1
        ];
        $this->internalTestGetIdByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertEquals($resultTableRow['transaction_id'], $this->model->getIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetLastStatusByOrderFail()
    {
        $orderId = 1;
        $resultTableRow = null;
        $this->internalTestGetLastByOrderId($orderId, $resultTableRow);
        $this->assertFalse($this->model->getLastStatusByOrderId($orderId));
    }

    public function testGetLastStatusByOrderSuccess()
    {
        $orderId = 1;
        $status = 'status';
        $resultTableRow = ['additional_information' => 'serialized data'];
        $this->serializer->expects($this->once())->method('unserialize')->with('serialized data')
            ->willReturn([
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [
                    'status' => $status
                ]
            ]);
        $this->internalTestGetLastByOrderId($orderId, $resultTableRow);
        $this->assertEquals($status, $this->model->getLastStatusByOrderId($orderId));
    }

    /**
     * @param $orderId
     * @param $resultTableRow
     */
    protected function internalTestGetLastPayuplOrderIdByOrderId($orderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->resource->expects($this->once())->method('getTableName')
            ->with($transactionTable)->willReturn($transactionTable);
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $select->expects($this->once())->method('from')->with(
            $this->equalTo(['main_table' => $transactionTable]),
            $this->equalTo(['txn_id'])
        )->will($this->returnSelf());
        $select->expects($this->at(1))->method('where')->with(
            $this->equalTo('order_id = ?'),
            $this->equalTo($orderId)
        )->will($this->returnSelf());
        $select->expects($this->at(2))->method('where')->with(
            $this->equalTo('txn_type = ?'),
            $this->equalTo('order')
        )->will($this->returnSelf());
        $select->expects($this->once())->method('order')
            ->with($this->equalTo('transaction_id ' . \Zend_Db_Select::SQL_DESC))->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->adapter->expects($this->once())->method('select')->willReturn($select);
        $this->adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))
            ->willReturn($resultTableRow);
    }

    /**
     * @param $payuplOrderId
     * @param $resultTableRow
     */
    protected function internalTestCheckIfNewestByPayuplOrderId($payuplOrderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $transactionTableReal = 'table';
        $this->resource->expects($this->once())->method('getTableName')->with($transactionTable)
            ->willReturn($transactionTableReal);
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $select->expects($this->once())->method('from')->with(
            $this->equalTo(['main_table' => $transactionTableReal]),
            $this->equalTo(['transaction_id'])
        )->will($this->returnSelf());
        $select->expects($this->once())->method('joinLeft')->with(
            $this->equalTo(['t2' => $transactionTableReal]),
            $this->equalTo('t2.order_id = main_table.order_id AND t2.transaction_id > main_table.transaction_id'),
            $this->equalTo(['newer_id' => 't2.transaction_id'])
        )->will($this->returnSelf());
        $select->expects($this->once())->method('where')->with(
            $this->equalTo('main_table.txn_id = ?'),
            $this->equalTo($payuplOrderId)
        )->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->adapter->expects($this->once())->method('select')->willReturn($select);
        $this->adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))
            ->willReturn($resultTableRow);
    }

    protected function internalTestGetOrderIdByPayuplOrderId($payuplOrderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->resource->expects($this->once())->method('getTableName')->with($transactionTable)
            ->willReturn($transactionTable);
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $select->expects($this->once())->method('from')->with(
            $this->equalTo(['main_table' => $transactionTable]),
            $this->equalTo(['order_id'])
        )->will($this->returnSelf());
        $select->expects($this->once())->method('where')->with(
            $this->equalTo('txn_id = ?'),
            $this->equalTo($payuplOrderId)
        )->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->adapter->expects($this->once())->method('select')->willReturn($select);
        $this->adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))
            ->willReturn($resultTableRow);
    }

    protected function internalTestGetAdditionalDataByPayuplOrderId($payuplOrderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->resource->expects($this->once())->method('getTableName')->with($transactionTable)
            ->willReturn($transactionTable);
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $select->expects($this->once())->method('from')->with(
            $this->equalTo(['main_table' => $transactionTable]),
            $this->equalTo(['additional_information'])
        )->will($this->returnSelf());
        $select->expects($this->once())->method('where')->with(
            $this->equalTo('txn_id = ?'),
            $this->equalTo($payuplOrderId)
        )->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->adapter->expects($this->once())->method('select')->willReturn($select);
        $this->adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))
            ->willReturn($resultTableRow);
    }

    /**
     * @param string $getFieldName
     * @param string $byFieldName
     * @param mixed $value
     * @param array|null $resultTableRow
     */
    protected function internalTestGetBy($getFieldName, $byFieldName, $value, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->resource->expects($this->once())->method('getTableName')->with($transactionTable)
            ->willReturn($transactionTable);
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $select->expects($this->once())->method('from')->with(
            $this->equalTo(['main_table' => $transactionTable]),
            $this->equalTo([$getFieldName])
        )->will($this->returnSelf());
        $select->expects($this->once())->method('where')->with(
            $this->equalTo($byFieldName . ' = ?'),
            $this->equalTo($value)
        )->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->adapter->expects($this->once())->method('select')->willReturn($select);
        $this->adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))
            ->willReturn($resultTableRow);
    }

    protected function internalTestGetLastByOrderId($orderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->resource->expects($this->once())->method('getTableName')->with($transactionTable)
            ->willReturn($transactionTable);
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $select->expects($this->once())->method('from')->with(
            $this->equalTo(['main_table' => $transactionTable]),
            $this->equalTo(['additional_information'])
        )->will($this->returnSelf());
        $select->expects($this->at(1))->method('where')->with(
            $this->equalTo('order_id = ?'),
            $this->equalTo($orderId)
        )->will($this->returnSelf());
        $select->expects($this->at(2))->method('where')->with(
            $this->equalTo('txn_type = ?'),
            $this->equalTo('order')
        )->will($this->returnSelf());
        $select->expects($this->once())->method('order')
            ->with($this->equalTo('transaction_id ' . \Zend_Db_Select::SQL_DESC))->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->adapter->expects($this->once())->method('select')->willReturn($select);
        $this->adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))
            ->willReturn($resultTableRow);
    }

    protected function internalTestGetIdByPayuplOrderId($payuplOrderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->resource->expects($this->once())->method('getTableName')->with($transactionTable)
            ->willReturn($transactionTable);
        $select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $select->expects($this->once())->method('from')->with(
            $this->equalTo(['main_table' => $transactionTable]),
            $this->equalTo(['transaction_id'])
        )->will($this->returnSelf());
        $select->expects($this->once())->method('where')->with(
            $this->equalTo('txn_id = ?'),
            $this->equalTo($payuplOrderId)
        )->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->adapter->expects($this->once())->method('select')->willReturn($select);
        $this->adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))
            ->willReturn($resultTableRow);
    }
}
