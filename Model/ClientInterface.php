<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

interface ClientInterface
{
    /**
     * @param array $data
     * @return bool|\OpenPayU_Result
     * @throws Client\Exception
     */
    public function orderCreate(array $data = []);

    /**
     * @param string $payuplOrderId
     * @return bool|\OpenPayU_Result
     * @throws Client\Exception
     */
    public function orderRetrieve($payuplOrderId);

    /**
     * @param string $payuplOrderId
     * @return bool|\OpenPayU_Result
     * @throws Client\Exception
     */
    public function orderCancel($payuplOrderId);

    /**
     * @param array $data
     * @return bool|\OpenPayU_Result
     * @throws Client\Exception
     */
    public function orderStatusUpdate(array $data = []);

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool|\OpenPayU_Result
     * @throws Client\Exception
     */
    public function orderConsumeNotification(\Magento\Framework\App\Request\Http $request);

    /**
     * @param string $orderId
     * @param string $description
     * @param int $amount
     * @return bool|\OpenPayU_Result
     * @throws Client\Exception
     */
    public function refundCreate($orderId = '', $description = '', $amount = null);

    /**
     * @return Client\OrderInterface
     */
    public function getOrderHelper();
}