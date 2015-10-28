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
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_clientFactory = $this->getMockBuilder(\Orba\Payupl\Model\ClientFactory::class)->disableOriginalConstructor()->getMock();
        $this->_scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();
        $this->_model = $objectManager->getObject(Paytype::class, [
            'clientFactory' => $this->_clientFactory,
            'scopeConfig' => $this->_scopeConfig
        ]);
    }

    public function testGetAllForQuotePaytypesDisabledInCheckout()
    {
        $quote = $this->_getQuoteMock();
        $this->_expectPaytypesInCheckout(false);
        $this->assertFalse($this->_model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteNoPaytypes()
    {
        $quote = $this->_getQuoteMock();
        $this->_expectPaytypesInCheckout(true);
        $client = $this->_getClientMock();
        $client->expects($this->once())->method('getPaytypes')->willReturn(false);
        $this->assertFalse($this->_model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteEmptyPaytypes()
    {
        $quote = $this->_getQuoteMock();
        $this->_expectPaytypesInCheckout(true);
        $client = $this->_getClientMock();
        $client->expects($this->once())->method('getPaytypes')->willReturn([]);
        $this->assertEquals([], $this->_model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteDisabledPaytype()
    {
        $quote = $this->_getQuoteMock();
        $this->_expectPaytypesInCheckout(true);
        $client = $this->_getClientMock();
        $paytype = $this->_getExemplaryPaytypeData();
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
        $this->assertEquals($this->_getPaytypeResultArray($paytype), $this->_model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteTooLowTotal()
    {
        $quote = $this->_getQuoteMock();
        $this->_expectPaytypesInCheckout(true);
        $client = $this->_getClientMock();
        $paytype = $this->_getExemplaryPaytypeData();
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
        $this->assertEquals($this->_getPaytypeResultArray($paytype), $this->_model->getAllForQuote($quote));
    }

    public function testGetAllForQuoteTooHighTotal()
    {
        $quote = $this->_getQuoteMock();
        $this->_expectPaytypesInCheckout(true);
        $client = $this->_getClientMock();
        $paytype = $this->_getExemplaryPaytypeData();
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
        $this->assertEquals($this->_getPaytypeResultArray($paytype), $this->_model->getAllForQuote($quote));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getClientMock()
    {
        $client = $this->getMockBuilder(\Orba\Payupl\Model\Client::class)->disableOriginalConstructor()->getMock();
        $this->_clientFactory->expects($this->once())->method('create')->willReturn($client);
        return $client;
    }

    /**
     * @return array
     */
    protected function _getExemplaryPaytypeData()
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
    protected function _getQuoteMock()
    {
        return $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->setMethods(['getGrandTotal'])->disableOriginalConstructor()->getMock();
    }

    /**
     * @param $paytypesFlag
     */
    protected function _expectPaytypesInCheckout($paytypesFlag)
    {
        $this->_scopeConfig->expects($this->once())->method('isSetFlag')->with(
            $this->equalTo(\Orba\Payupl\Model\Payupl::XML_PATH_PAYTYPES_IN_CHECKOUT),
            $this->equalTo('store')
        )->willReturn($paytypesFlag);
    }

    /**
     * @param $paytype
     * @return array
     */
    protected function _getPaytypeResultArray($paytype)
    {
        return [$paytype + ['id' => 'orba-payupl-paytype-' . $paytype['type']]];
    }
}