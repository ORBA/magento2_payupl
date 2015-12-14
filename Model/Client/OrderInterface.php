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
     * Returns false on fail or array with the following keys on success: orderId, redirectUri, extOrderId
     *
     * @param array $data
     * @return array|false
     */
    public function create(array $data);

    /**
     * Return false on fail or array with the following keys: status, amount on success.
     *
     * @param string $payuplOrderId
     * @return array|false
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
     * Returns false on fail or array with the following keys on success: payuplOrderId, status, amount
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return array|false
     */
    public function consumeNotification(\Magento\Framework\App\Request\Http $request);

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getDataForOrderCreate(\Magento\Sales\Model\Order $order);

    /**
     * Adds API-related special data to standard order data.
     *
     * @param array $data
     * @return array
     */
    public function addSpecialDataToOrder(array $data = []);

    /**
     * @return string
     */
    public function getNewStatus();

    /**
     * Checks if payment was successful.
     *
     * @return bool
     */
    public function paymentSuccessCheck();

    /**
     * @param string $payuplOrderId
     * @return bool
     */
    public function canProcessNotification($payuplOrderId);

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @param float $amount
     * @return \Magento\Framework\Controller\Result\Raw
     * @throws Exception
     */
    public function processNotification($payuplOrderId, $status, $amount);

    /**
     * Returns false if there is no API for paytypes.
     *
     * @return false|array
     */
    public function getPaytypes();

    /**
     * @param mixed $status
     * @return string
     */
    public function getStatusDescription($status);
}
