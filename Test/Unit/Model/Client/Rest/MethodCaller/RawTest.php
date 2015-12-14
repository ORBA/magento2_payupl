<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\MethodCaller;

use Orba\Payupl\Test\Util;
use Orba\Payupl\Model\Client\Exception;

class RawTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Raw
     */
    protected $model;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(Raw::class, []);
    }

    public function testGetResponseWithoutStatus()
    {
        $result = $this->getResultMock();
        $response = true;
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $this->assertEquals($response, Util::callMethod($this->model, 'getResponse', [$result]));
    }

    public function testGetResponseStatusSuccess()
    {
        $result = $this->getResultMock();
        $response = (object) [
            'status' => (object) [
                'statusCode' => 'SUCCESS'
            ]
        ];
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $this->assertEquals($response, Util::callMethod($this->model, 'getResponse', [$result]));
    }

    public function testGetResponseStatusOther()
    {
        $result = $this->getResultMock();
        $response = (object) [
            'status' => (object) [
                'statusCode' => 'OTHER'
            ]
        ];
        $result->expects($this->once())->method('getResponse')->willReturn($response);
        $this->setExpectedException(Exception::class, \Zend_Json::encode($response->status));
        Util::callMethod($this->model, 'getResponse', [$result]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResultMock()
    {
        return $this->getMockBuilder(\OpenPayU_Result::class)->disableOriginalConstructor()->setMethods(['getResponse'])
            ->getMock();
    }
}
