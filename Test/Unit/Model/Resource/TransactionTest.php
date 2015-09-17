<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Resource;

use \Orba\Payupl\Test\Util;

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

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_date = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManager->getObject(Transaction::class, [
            'date' => $this->_date
        ]);
    }
    
    public function testSaveCreationTimeExistingModel()
    {
        $transaction = $this->getMockBuilder(\Orba\Payupl\Model\Transaction::class)->disableOriginalConstructor()->getMock();
        $transaction->expects($this->once())->method('isObjectNew')->willReturn(false);
        $transaction->expects($this->never())->method('setCreatedAt');
        $this->assertEquals(Util::callMethod($this->_model, '_beforeSave', [$transaction]), $this->_model);
    }

    public function testSaveCreationTimeNewModel()
    {
        $transaction = $this->getMockBuilder(\Orba\Payupl\Model\Transaction::class)->setMethods([
            'isObjectNew',
            'setCreatedAt'
        ])->disableOriginalConstructor()->getMock();
        $transaction->expects($this->once())->method('isObjectNew')->willReturn(true);
        $date = 'date';
        $this->_date->expects($this->once())->method('formatDate')->with($this->equalTo(true))->willReturn($date);
        $transaction->expects($this->once())->method('setCreatedAt')->with($this->equalTo($date));
        $this->assertEquals(Util::callMethod($this->_model, '_beforeSave', [$transaction]), $this->_model);
    }
}