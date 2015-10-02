<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Sales;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Order
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject(Order::class, []);
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf(Order\Config::class, $this->_model->getConfig());
    }
}