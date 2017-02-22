<?php
/**
 * @copyright Copyright (c) 2017 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ClassicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Orba\Payupl\Model\Client\Classic
     */
    protected $model;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $configHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Classic\Config::class)->disableOriginalConstructor()->getMock();
        $orderHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Classic\Order::class)->disableOriginalConstructor()->getMock();
        $refundHelper = $this->getMockBuilder(\Orba\Payupl\Model\Client\Classic\Refund::class)->disableOriginalConstructor()->getMock();

        $this->model = $objectManagerHelper->getObject(
            \Orba\Payupl\Model\Client\Classic::class,
            [
                'configHelper' => $configHelper,
                'orderHelper' =>  $orderHelper,
                'refundHelper' => $refundHelper
            ]
        );
    }

    public function testGetType()
    {
        $this->assertNotEmpty($this->model->getType());
        $this->assertEquals(\Orba\Payupl\Model\Client::TYPE_CLASSIC, $this->model->getType());
    }
}
