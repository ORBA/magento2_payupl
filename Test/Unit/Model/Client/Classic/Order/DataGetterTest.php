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
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extOrderIdHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->extOrderIdHelper = $this->getMockBuilder(\Orba\Payupl\Model\Order\ExtOrderId::class)
            ->disableOriginalConstructor()->getMock();
        $this->configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Classic\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()->getMock();
        $this->session = $this->getMockBuilder(\Orba\Payupl\Model\Session::class)
            ->setMethods(['getPaytype', 'setPaytype'])->disableOriginalConstructor()->getMock();
        $this->model = $objectManagerHelper->getObject(
            DataGetter::class,
            [
                'extOrderIdHelper' => $this->extOrderIdHelper,
                'configHelper' => $this->configHelper,
                'dateTime' => $this->dateTime,
                'session' => $this->session
            ]
        );
    }
    
    public function testGetBasicDataNoPaytype()
    {
        $incrementId = '0000000001';
        $amount = '10.9800';
        $desc = __('Order # %1', [$incrementId]);
        $firstName = 'Jan';
        $lastName = 'Kowalski';
        $email = 'jan.kowalski@orba.pl';
        $sessionId = '0000000001-1';
        $order = $this->getOrderMockWithExpectationsForBasicData($incrementId, $firstName, $lastName, $email, $amount);
        $this->extOrderIdHelper->expects($this->once())->method('generate')->with($this->equalTo($order))
            ->willReturn($sessionId);
        $this->session->expects($this->once())->method('getPaytype')->willReturn(null);
        $this->assertEquals([
            'amount' => $amount * 100,
            'desc' => $desc,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'session_id' => $sessionId,
            'order_id' => $incrementId
        ], $this->model->getBasicData($order));
    }

    public function testGetBasicDataWithPaytype()
    {
        $incrementId = '0000000001';
        $amount = '10.9800';
        $desc = __('Order # %1', [$incrementId]);
        $firstName = 'Jan';
        $lastName = 'Kowalski';
        $email = 'jan.kowalski@orba.pl';
        $sessionId = '0000000001-1';
        $paytype = 't';
        $order = $this->getOrderMockWithExpectationsForBasicData($incrementId, $firstName, $lastName, $email, $amount);
        $this->extOrderIdHelper->expects($this->once())->method('generate')->with($this->equalTo($order))
            ->willReturn($sessionId);
        $this->session->expects($this->once())->method('getPaytype')->willReturn($paytype);
        $this->session->expects($this->once())->method('setPaytype')->with($this->equalTo(null));
        $this->assertEquals([
            'amount' => $amount * 100,
            'desc' => $desc,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'session_id' => $sessionId,
            'order_id' => $incrementId,
            'pay_type' => $paytype
        ], $this->model->getBasicData($order));
    }

    public function testPosId()
    {
        $posId = '123456';
        $this->configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('pos_id'))
            ->willReturn($posId);
        $this->assertEquals($posId, $this->model->getPosId());
    }

    public function testPosAuthKey()
    {
        $posAuthKey = 'ABC';
        $this->configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('pos_auth_key'))
            ->willReturn($posAuthKey);
        $this->assertEquals($posAuthKey, $this->model->getPosAuthKey());
    }

    public function testClientIp()
    {
        $ip = '127.0.0.1';
        $_SERVER['REMOTE_ADDR'] = $ip;
        $this->assertEquals($ip, $this->model->getClientIp());
    }

    public function testTs()
    {
        $ts = 12345678;
        $this->dateTime->expects($this->once())->method('timestamp')->willReturn($ts);
        $this->assertEquals($ts, $this->model->getTs());
    }

    public function testSigForOrderCreateNoPaytype()
    {
        $data = $this->getExemplaryOrderCreateData();
        $keyMd5 = 'GHI';
        $this->configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('key_md5'))
            ->willReturn($keyMd5);
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
        $this->assertEquals($sig, $this->model->getSigForOrderCreate($data));
    }

    public function testSigForOrderCreatePaytype()
    {
        $data = $this->getExemplaryOrderCreateData();
        $data['pay_type'] = 't';
        $keyMd5 = 'GHI';
        $this->configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('key_md5'))
            ->willReturn($keyMd5);
        $sig = md5(
            $data['pos_id'] .
            $data['pay_type'] .
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
        $this->assertEquals($sig, $this->model->getSigForOrderCreate($data));
    }

    public function testSigForOrderRetrieve()
    {
        $data = [
            'pos_id' => '123456',
            'session_id' => 'ABC',
            'ts' => 12345678
        ];
        $keyMd5 = 'GHI';
        $this->configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('key_md5'))
            ->willReturn($keyMd5);
        $sig = md5(
            $data['pos_id'] .
            $data['session_id'] .
            $data['ts'] .
            $keyMd5
        );
        $this->assertEquals($sig, $this->model->getSigForOrderRetrieve($data));
    }

    /**
     * @param $incrementId
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $amount
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMockWithExpectationsForBasicData($incrementId, $firstName, $lastName, $email, $amount)
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $billingAddress = $this->getMockBuilder(\Magento\Sales\Model\Order\Address::class)->disableOriginalConstructor()
            ->getMock();
        $billingAddress->expects($this->once())->method('getFirstname')->willReturn($firstName);
        $billingAddress->expects($this->once())->method('getLastname')->willReturn($lastName);
        $order->expects($this->once())->method('getBillingAddress')->willReturn($billingAddress);
        $order->expects($this->once())->method('getIncrementId')->willReturn($incrementId);
        $order->expects($this->once())->method('getCustomerEmail')->willReturn($email);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($amount);
        return $order;
    }

    /**
     * @return array
     */
    protected function getExemplaryOrderCreateData()
    {
        return [
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
    }
}
