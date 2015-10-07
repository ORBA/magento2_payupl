<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Orba\Payupl\Model\Client\Exception;

class Order implements \Orba\Payupl\Model\Client\OrderInterface
{
    /**
     * @param $id
     * @return bool
     */
    public function validateCancel($id)
    {
        // TODO: Implement validateCancel() method.
    }

    /**
     * Returns false on fail or array with the following keys on success: orderId, redirectUri, extOrderId
     *
     * @param array $data
     * @return array|false
     */
    public function create(array $data)
    {
        // TODO: Implement create() method.
    }

    /**
     * Return false on fail or transaction status on success.
     *
     * @param string $payuplOrderId
     * @return string|false
     */
    public function retrieve($payuplOrderId)
    {
        // TODO: Implement retrieve() method.
    }

    /**
     * Return false on fail or true success.
     *
     * @param string $payuplOrderId
     * @return bool
     */
    public function cancel($payuplOrderId)
    {
        // TODO: Implement cancel() method.
    }

    /**
     * Return false on fail or true success.
     *
     * @param array $data
     * @return bool
     */
    public function statusUpdate(array $data = [])
    {
        // TODO: Implement statusUpdate() method.
    }

    /**
     * Returns false on fail or array with the following keys on success: orderId, status
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return array|false
     */
    public function consumeNotification(\Magento\Framework\App\Request\Http $request)
    {
        // TODO: Implement consumeNotification() method.
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getDataForOrderCreate(\Magento\Sales\Model\Order $order)
    {
        // TODO: Implement getDataForOrderCreate() method.
    }

    /**
     * @return string
     */
    public function getNewStatus()
    {
        // TODO: Implement getNewStatus() method.
    }

    /**
     * Checks if payment was successful.
     *
     * @return bool
     */
    public function paymentSuccessCheck()
    {
        // TODO: Implement paymentSuccessCheck() method.
    }

    /**
     * @param string $payuplOrderId
     * @return bool
     */
    public function canProcessNotification($payuplOrderId)
    {
        // TODO: Implement canProcessNotification() method.
    }

    /**
     * @param string $payuplOrderId
     * @param string $status
     * @param float $amount
     * @return \Magento\Framework\Controller\Result\Raw
     * @throws Exception
     */
    public function processNotification($payuplOrderId, $status, $amount)
    {
        // TODO: Implement processNotification() method.
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateCreate(array $data = [])
    {
        // TODO: Implement validateCreate() method.
    }

    /**
     * @param $id
     * @return bool
     */
    public function validateRetrieve($id)
    {
        // TODO: Implement validateRetrieve() method.
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateStatusUpdate(array $data = [])
    {
        // TODO: Implement validateStatusUpdate() method.
    }

    public function addSpecialDataToOrder(array $data = [])
    {
        // TODO: Implement addSpecialDataToOrder() method.
    }
}