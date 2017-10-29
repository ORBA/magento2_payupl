<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\Order;

use Magento\Framework\Exception\LocalizedException;

class NotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Notification
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configHelper;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Classic\Config::class)
            ->disableOriginalConstructor()->getMock();
        $this->model = $objectManager->getObject(Notification::class, [
            'configHelper' => $this->configHelper
        ]);
    }

    public function testConsumeNotificationFailNoPost()
    {
        $request = $this->getRequestMock();
        $request->expects($this->once())->method('isPost')->willReturn(false);
        $this->expectException(LocalizedException::class, 'POST request is required.');
        $this->model->getPayuplOrderId($request);
    }

    public function testConsumeNotificationFailInvaidSig()
    {
        $sig = 'ABC';
        $ts = '123';
        $posId = '123456';
        $sessionId = 'DEF';
        $secondKeyMd5 = 'GHI';
        $request = $this->getRequestMock();
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->at(1))->method('getParam')->with($this->equalTo('sig'))->willReturn($sig);
        $request->expects($this->at(2))->method('getParam')->with($this->equalTo('ts'))->willReturn($ts);
        $request->expects($this->at(3))->method('getParam')->with($this->equalTo('pos_id'))->willReturn($posId);
        $request->expects($this->at(4))->method('getParam')->with($this->equalTo('session_id'))->willReturn($sessionId);
        $this->configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('second_key_md5'))
            ->willReturn($secondKeyMd5);
        $this->expectException(LocalizedException::class, 'Invalid SIG.');
        $this->model->getPayuplOrderId($request);
    }

    public function testConsumeNotificationSuccess()
    {
        $ts = '123';
        $posId = '123456';
        $sessionId = 'DEF';
        $secondKeyMd5 = 'GHI';
        $sig = md5($posId . $sessionId . $ts . $secondKeyMd5);
        $request = $this->getRequestMock();
        $request->expects($this->once())->method('isPost')->willReturn(true);
        $request->expects($this->at(1))->method('getParam')->with($this->equalTo('sig'))->willReturn($sig);
        $request->expects($this->at(2))->method('getParam')->with($this->equalTo('ts'))->willReturn($ts);
        $request->expects($this->at(3))->method('getParam')->with($this->equalTo('pos_id'))->willReturn($posId);
        $request->expects($this->at(4))->method('getParam')->with($this->equalTo('session_id'))->willReturn($sessionId);
        $this->configHelper->expects($this->once())->method('getConfig')->with($this->equalTo('second_key_md5'))
            ->willReturn($secondKeyMd5);
        $this->assertEquals($sessionId, $this->model->getPayuplOrderId($request));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestMock()
    {
        return $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->setMethods(['isPost', 'getParam'])
            ->disableOriginalConstructor()->getMock();
    }
}
