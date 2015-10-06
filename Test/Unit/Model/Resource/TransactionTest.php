<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Resource;

use Magento\Framework\DB\Adapter\Pdo\Mysql as Adapter;
use Magento\Framework\DB\Select;
use Orba\Payupl\Test\Util;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Transaction
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_date;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_date = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)->disableOriginalConstructor()->getMock();
        $this->_resource = $this->getMockBuilder(\Magento\Framework\App\Resource::class)->disableOriginalConstructor()->getMock();
        $this->_adapter = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)->getMockForAbstractClass();
        $this->_resource->expects($this->any())->method('getConnection')->willReturn($this->_adapter);
        $context = $this->getMockBuilder(\Magento\Framework\Model\Resource\Db\Context::class)->disableOriginalConstructor()->getMock();
        $context->expects($this->once())->method('getResources')->willReturn($this->_resource);
        $this->_model = $objectManager->getObject(Transaction::class, [
            'context' => $context,
            'date' => $this->_date
        ]);
    }
    
    public function testGetLastPayuplOrderIdByOrderIdFail()
    {
        $orderId = 1;
        $resultTableRow = null;
        $this->_testGetLastPayuplOrderIdByOrderId($orderId, $resultTableRow);
        $this->assertFalse($this->_model->getLastPayuplOrderIdByOrderId($orderId));
    }

    public function testGetLastPayuplOrderIdByOrderIdSuccess()
    {
        $orderId = 1;
        $resultTableRow = [
            'txn_id' => 'ABC'
        ];
        $this->_testGetLastPayuplOrderIdByOrderId($orderId, $resultTableRow);
        $this->assertEquals($resultTableRow['txn_id'], $this->_model->getLastPayuplOrderIdByOrderId($orderId));
    }

    public function testCheckIfNewestByPayuplOrderIdFailNotFound()
    {
        $payuplOrderId = '123';
        $resultTableRow = null;
        $this->_testCheckIfNewestByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->_model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testCheckIfNewestByPayuplOrderIdFailOld()
    {
        $payuplOrderId = '123';
        $resultTableRow = [
            'newer_id' => 2
        ];
        $this->_testCheckIfNewestByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->_model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testCheckIfNewestByPayuplOrderIdSuccess()
    {
        $payuplOrderId = '123';
        $resultTableRow = [
            'newer_id' => null
        ];
        $this->_testCheckIfNewestByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertTrue($this->_model->checkIfNewestByPayuplOrderId($payuplOrderId));
    }

    public function testGetOrderIdByPayuplOrderIdFail()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = null;
        $this->_testGetOrderIdByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->_model->getOrderIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetOrderIdByPayuplOrderIdSuccess()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = [
            'order_id' => 1
        ];
        $this->_testGetOrderIdByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertEquals($resultTableRow['order_id'], $this->_model->getOrderIdByPayuplOrderId($payuplOrderId));
    }

    public function testGetStatusByPayuplOrderIdFail()
    {
        $payuplOrderId = 'ABC';
        $resultTableRow = null;
        $this->_testGetStatusByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertFalse($this->_model->getStatusByPayuplOrderId($payuplOrderId));
    }

    public function testGetStatusByPayuplOrderIdSuccess()
    {
        $payuplOrderId = 'ABC';
        $status = 'PENDING';
        $resultTableRow = [
            'additional_information' => serialize([
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [
                    'status' => $status
                ]
            ])
        ];
        $this->_testGetStatusByPayuplOrderId($payuplOrderId, $resultTableRow);
        $this->assertEquals($status, $this->_model->getStatusByPayuplOrderId($payuplOrderId));
    }

    public function testGetLastTryByOrderIdFirst()
    {
        $orderId = 1;
        $resultTableRow = null;
        $this->_testGetLastTryByOrderId($orderId, $resultTableRow);
        $this->assertEquals(0, $this->_model->getLastTryByOrderId($orderId));
    }

    public function testGetLastTryByOrderIdNotFirst()
    {
        $orderId = 1;
        $try = 2;
        $resultTableRow = [
            'additional_information' => serialize([
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => [
                    'try' => $try
                ]
            ])
        ];
        $this->_testGetLastTryByOrderId($orderId, $resultTableRow);
        $this->assertEquals($try, $this->_model->getLastTryByOrderId($orderId));
    }

    /**
     * @param $orderId
     * @param $resultTableRow
     */
    protected function _testGetLastPayuplOrderIdByOrderId($orderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->_resource->expects($this->once())->method('getTableName')->with($transactionTable)->willReturn($transactionTable);
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
        $select->expects($this->once())->method('order')->with($this->equalTo('transaction_id ' . \Zend_Db_Select::SQL_DESC))->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->_adapter->expects($this->once())->method('select')->willReturn($select);
        $this->_adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))->willReturn($resultTableRow);
    }

    /**
     * @param $payuplOrderId
     * @param $resultTableRow
     */
    protected function _testCheckIfNewestByPayuplOrderId($payuplOrderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $transactionTableReal = 'table';
        $this->_resource->expects($this->once())->method('getTableName')->with($transactionTable)->willReturn($transactionTableReal);
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
        $this->_adapter->expects($this->once())->method('select')->willReturn($select);
        $this->_adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))->willReturn($resultTableRow);
    }

    protected function _testGetOrderIdByPayuplOrderId($payuplOrderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->_resource->expects($this->once())->method('getTableName')->with($transactionTable)->willReturn($transactionTable);
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
        $this->_adapter->expects($this->once())->method('select')->willReturn($select);
        $this->_adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))->willReturn($resultTableRow);
    }

    protected function _testGetStatusByPayuplOrderId($payuplOrderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->_resource->expects($this->once())->method('getTableName')->with($transactionTable)->willReturn($transactionTable);
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
        $this->_adapter->expects($this->once())->method('select')->willReturn($select);
        $this->_adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))->willReturn($resultTableRow);
    }

    /**
     * @param string $getFieldName
     * @param string $byFieldName
     * @param mixed $value
     * @param array|null $resultTableRow
     */
    protected function _testGetBy($getFieldName, $byFieldName, $value, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->_resource->expects($this->once())->method('getTableName')->with($transactionTable)->willReturn($transactionTable);
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
        $this->_adapter->expects($this->once())->method('select')->willReturn($select);
        $this->_adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))->willReturn($resultTableRow);
    }

    protected function _testGetLastTryByOrderId($orderId, $resultTableRow)
    {
        $transactionTable = 'sales_payment_transaction';
        $this->_resource->expects($this->once())->method('getTableName')->with($transactionTable)->willReturn($transactionTable);
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
        $select->expects($this->once())->method('order')->with($this->equalTo('transaction_id ' . \Zend_Db_Select::SQL_DESC))->will($this->returnSelf());
        $select->expects($this->once())->method('limit')->with($this->equalTo(1))->will($this->returnSelf());
        $this->_adapter->expects($this->once())->method('select')->willReturn($select);
        $this->_adapter->expects($this->once())->method('fetchRow')->with($this->equalTo($select))->willReturn($resultTableRow);
    }
}