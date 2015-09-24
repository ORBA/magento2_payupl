<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

interface OrderInterface
{
    /**
     * @param array $data
     * @return bool
     */
    public function validateCreate(array $data = []);

    /**
     * @param $id
     * @return bool
     */
    public function validateRetrieve($id);

    /**
     * @param $id
     * @return bool
     */
    public function validateCancel($id);

    /**
     * @param array $data
     * @return bool
     */
    public function validateStatusUpdate(array $data = []);

    /**
     * @param array $data
     * @return bool
     */
    public function validateConsumeNotification(array $data = []);

    /**
     * Returns false on fail or array with the following keys on success: orderId, redirectUri, extOrderId
     *
     * @param array $data
     * @return array|false
     */
    public function create(array $data);

    /**
     * Return false on fail or transaction status on success.
     *
     * @param string $payuplOrderId
     * @return string|false
     */
    public function retrieve($payuplOrderId);

    /**
     * Return false on fail or true success.
     *
     * @param string $payuplOrderId
     * @return bool
     */
    public function cancel($payuplOrderId);

    /**
     * Return false on fail or true success.
     *
     * @param array $data
     * @return bool
     */
    public function statusUpdate(array $data = []);

    /**
     * Returns false on fail or array with the following keys on success: orderId, status
     *
     * @param array $data
     * @return array|false
     */
    public function consumeNotification(array $data = []);

    /**
     * @param int $orderId
     * @return bool|\Magento\Sales\Model\Order
     */
    public function loadOrderById($orderId);

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getDataForOrderCreate(\Magento\Sales\Model\Order $order);

    /**
     * @param int $orderId
     * @param string $payuplOrderId
     * @param string $payuplExternalOrderId
     * @return void
     */
    public function saveNewTransaction($orderId, $payuplOrderId, $payuplExternalOrderId);

    /**
     * @return string
     */
    public function getNewStatus();

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function setNewOrderStatus(\Magento\Sales\Model\Order $order);

    /**
     * Checks if payment was successful basing on controller request.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function paymentSuccessCheck(\Magento\Framework\App\RequestInterface $request);
}