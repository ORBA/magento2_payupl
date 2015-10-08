<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataGetterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataGetter
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_extOrderIdHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dateTime;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_extOrderIdHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order\ExtOrderId::class)->disableOriginalConstructor()->getMock();
        $this->_configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Classic\Config::class)->disableOriginalConstructor()->getMock();
        $this->_dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)->disableOriginalConstructor()->getMock();
        $this->_model = $objectManagerHelper->getObject(
            DataGetter::class,
            [
                'extOrderIdHelper' => $this->_extOrderIdHelper,
                'configHelper' => $this->_configHelper,
                'dateTime' => $this->_dateTime
            ]
        );
    }
    
    public function testGetBasicData()
    {
        $incrementId = '0000000001';
        $amount = '10.9800';
        $desc = __('Order # %1', [$incrementId]);
        $firstName = 'Jan';
        $lastName = 'Kowalski';
        $email = 'jan.kowalski@orba.pl';
        $sessionId = '0000000001-1';
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $order->expects($this->once())->method('getIncrementId')->willReturn($incrementId);
        $order->expects($this->once())->method('getCustomerFirstname')->willReturn($firstName);
        $order->expects($this->once())->method('getCustomerLastname')->willReturn($lastName);
        $order->expects($this->once())->method('getCustomerEmail')->willReturn($email);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($amount);
        $this->_extOrderIdHelper->expects($this->once())->method('generate')->with($this->equalTo($order))->willReturn($sessionId);
        $this->assertEquals([
            'amount' => $amount * 100,
            'desc' => $desc,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'session_id' => $sessionId,
            'order_id' => $incrementId
        ], $this->_model->getBasicData($order));
    }

    public function testPosId()
    {
        $posId = '123456';
        $this->_configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('pos_id'))->willReturn($posId);
        $this->assertEquals($posId, $this->_model->getPosId());
    }

    public function testPosAuthKey()
    {
        $posAuthKey = 'ABC';
        $this->_configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('pos_auth_key'))->willReturn($posAuthKey);
        $this->assertEquals($posAuthKey, $this->_model->getPosAuthKey());
    }

    public function testClientIp()
    {
        $ip = '127.0.0.1';
        $_SERVER['REMOTE_ADDR'] = $ip;
        $this->assertEquals($ip, $this->_model->getClientIp());
    }

    public function testTs()
    {
        $ts = 12345678;
        $this->_dateTime->expects($this->once())->method('timestamp')->willReturn($ts);
        $this->assertEquals($ts, $this->_model->getTs());
    }

    public function testSigForOrderCreate()
    {
        $data = [
            'pos_id' => '123456',
            'session_id' => 'ABC',
            'pos_auth_key' => 'DEF',
            'amount' => 101,
            'desc' => 'Desc',
            'order_id' => '100000001',
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@orba.pl',
            'client_ip' => '127.0.0.1',
            'ts' => 12345678
        ];
        $keyMd5 = 'GHI';
        $this->_configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('key_md5'))->willReturn($keyMd5);
        $sig = md5(
            $data['pos_id'] .
            $data['session_id'] .
            $data['pos_auth_key'] .
            $data['amount'] .
            $data['desc'] .
            $data['order_id'] .
            $data['first_name'] .
            $data['last_name'] .
            $data['email'] .
            $data['client_ip'] .
            $data['ts'] .
            $keyMd5
        );
        $this->assertEquals($sig, $this->_model->getSigForOrderCreate($data));
    }
}