<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class DataGetter
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getBasicData(\Magento\Sales\Model\Order $order)
    {
        $incrementId = $order->getIncrementId();
        return [
            'currencyCode' => $order->getOrderCurrencyCode(),
            'totalAmount' => $order->getGrandTotal() * 100,
            'extOrderId' => $incrementId,
            'description' => __('Order # %1', [$incrementId]),
        ];
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getProductsData(\Magento\Sales\Model\Order $order)
    {
        /**
         * @var $orderItem \Magento\Sales\Api\Data\OrderItemInterface
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

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array|null
     */
    public function getBuyerData(\Magento\Sales\Model\Order $order)
    {
        /**
         * @var $billingAddress \Magento\Sales\Api\Data\OrderAddressInterface
         */
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $buyer = [
                'email' => $billingAddress->getEmail(),
                'phone' => $billingAddress->getTelephone(),
                'firstName' => $billingAddress->getFirstname(),
                'lastName' => $billingAddress->getLastname()
            ];
            return $buyer;
        }
        return null;
    }
}