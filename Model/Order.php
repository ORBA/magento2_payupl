<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

class Order
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Order\DataGetter
     */
    protected $_dataGetter;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param Order\DataGetter $dataGetter
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Order\DataGetter $dataGetter
    )
    {
        $this->_orderFactory = $orderFactory;
        $this->_dataGetter = $dataGetter;
    }

    /**
     * @param string $orderId
     * @return array
     * @throws Order\Exception
     */
    public function getDataForNewTransaction($orderId)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $this->_orderFactory->create();
        $order->load($orderId);
        if ($order->getId()) {
            $data = ['products' => $this->_dataGetter->getProductsData($order)];
            $buyerData = $this->_dataGetter->getBuyerData($order);
            if ($buyerData) {
                $data['buyer'] = $buyerData;
            }
            $basicData = $this->_dataGetter->getBasicData($order);
            return array_merge($basicData, $data);
        } else {
            throw new Order\Exception('Order with ID ' . $orderId . ' does not exist.');
        }
    }
}