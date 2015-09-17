<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Order;

class DataGetter
{
    /**
     * @var DataGetter\ExtOrderId
     */
    protected $_extOrderIdHelper;

    public function __construct(
        DataGetter\ExtOrderId $extOrderIdHelper
    )
    {
        $this->_extOrderIdHelper = $extOrderIdHelper;
    }

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
            'extOrderId' => $this->_extOrderIdHelper->generate($order),
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
    public function getShippingData(\Magento\Sales\Model\Order $order)
    {
        if ($order->getShippingMethod()) {
            $shippingInclTax = (float) $order->getShippingInclTax();
            if ($shippingInclTax) {
                return [
                    'name' => __('Shipping Method') . ': ' . $order->getShippingDescription(),
                    'unitPrice' => $shippingInclTax * 100,
                    'quantity' => 1
                ];
            }
        }
        return null;
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