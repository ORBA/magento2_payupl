<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class PaytypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Paytype
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->model = $objectManager->getObject(Paytype::class, [
            'clientFactory' => $this->clientFactory,
            'scopeConfig' => $this->scopeConfig
        ]);
    }

    public function testGetAllForQuotePaytypesDisabledInCheckout()
    {
        $quote = $this->getQuoteMock();
        $this->expectPaytypesInCheckout(false);
        $this->assertFalse($this->model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteNoPaytypes()
    {
        $quote = $this->getQuoteMock();
        $this->expectPaytypesInCheckout(true);
        $client = $this->getClientMock();
        $client->expects($this->once())->method('getPaytypes')->willReturn(false);
        $this->assertFalse($this->model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteEmptyPaytypes()
    {
        $quote = $this->getQuoteMock();
        $this->expectPaytypesInCheckout(true);
        $client = $this->getClientMock();
        $client->expects($this->once())->method('getPaytypes')->willReturn([]);
        $this->assertEquals([], $this->model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteDisabledPaytype()
    {
        $quote = $this->getQuoteMock();
        $this->expectPaytypesInCheckout(true);
        $client = $this->getClientMock();
        $paytype = $this->getExemplaryPaytypeData();
        $client->expects($this->once())->method('getPaytypes')->willReturn([
            $paytype,
            [
                'type' => 'b',
                'name' => 'disabled',
                'enable' => false,
                'img' => 'image2',
                'min' => 0.5,
                'max' => 1000
            ]
        ]);
        $quote->expects($this->once())->method('getGrandTotal')->willReturn('10.99');
        $this->assertEquals($this->getPaytypeResultArray($paytype), $this->model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteTooLowTotal()
    {
        $quote = $this->getQuoteMock();
        $this->expectPaytypesInCheckout(true);
        $client = $this->getClientMock();
        $paytype = $this->getExemplaryPaytypeData();
        $client->expects($this->once())->method('getPaytypes')->willReturn([
            $paytype,
            [
                'type' => 'b',
                'name' => 'too low total',
                'enable' => true,
                'img' => 'image2',
                'min' => 0.5,
                'max' => 1000
            ]
        ]);
        $quote->expects($this->once())->method('getGrandTotal')->willReturn('0.49');
        $this->assertEquals($this->getPaytypeResultArray($paytype), $this->model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteTooHighTotal()
    {
        $quote = $this->getQuoteMock();
        $this->expectPaytypesInCheckout(true);
        $client = $this->getClientMock();
        $paytype = $this->getExemplaryPaytypeData();
        $client->expects($this->once())->method('getPaytypes')->willReturn([
            $paytype,
            [
                'type' => 'b',
                'name' => 'too high total',
                'enable' => true,
                'img' => 'image2',
                'min' => 0.5,
                'max' => 1000
            ]
        ]);
        $quote->expects($this->once())->method('getGrandTotal')->willReturn('1000.01');
        $this->assertEquals($this->getPaytypeResultArray($paytype), $this->model->getAllForQuote($quote));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }

    /**
     * @return array
     */
    protected function getExemplaryPaytypeData()
    {
        return [
            'type' => 'a',
            'name' => 'ok',
            'enable' => true,
            'img' => 'image1',
            'min' => 0,
            'max' => 999999
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteMock()
    {
        return $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->setMethods(['getGrandTotal'])
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @param $paytypesFlag
     */
    protected function expectPaytypesInCheckout($paytypesFlag)
    {
        $this->scopeConfig->expects($this->once())->method('isSetFlag')->with(
            $this->equalTo(\Orba\Payupl\Model\Payupl::XML_PATH_PAYTYPES_IN_CHECKOUT),
            $this->equalTo('store')
        )->willReturn($paytypesFlag);
    }

    /**
     * @param $paytype
     * @return array
     */
    protected function getPaytypeResultArray($paytype)
    {
        return [$paytype + ['id' => 'orba-payupl-paytype-' . $paytype['type']]];
    }
}
