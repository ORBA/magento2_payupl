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
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        $this->_orderFactory = $orderFactory;
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
            $incrementId = $order->getIncrementId();
            return [
                'currencyCode' => $order->getOrderCurrencyCode(),
                'totalAmount' => $order->getGrandTotal() * 100,
                'extOrderId' => $incrementId,
                'description' => __('Order # %1', [$incrementId]),
                'products' => $this->_getProducts($order)
            ];
        } else {
            throw new Order\Exception('Order with ID ' . $orderId . ' does not exist.');
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function _getProducts(\Magento\Sales\Model\Order $order)
    {
        /**
         * @var $orderItem \Magento\Sales\Model\Order\Item
         */
        $products = [];
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $orderItem) {
            $products[] = [
                'name' => $orderItem->getName(),
                'unitPrice' => $orderItem->getPriceInclTax() * 100,
                'quantity' => (float) $orderItem->getQtyOrdered()
            ];
        }
        return $products;
    }
}