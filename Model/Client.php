<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Orba\Payupl\Model\Client\Exception;

class Client
{
    /**
     * @var Client\OrderInterface
     */
    protected $orderHelper;

    /**
     * @var Client\RefundInterface
     */
    protected $refundHelper;

    /**
     * @param Client\ConfigInterface $configHelper
     * @param Client\OrderInterface $orderHelper
     * @param Client\RefundInterface $refundHelper
     */
    public function __construct(
        Client\ConfigInterface $configHelper,
        Client\OrderInterface $orderHelper,
        Client\RefundInterface $refundHelper
    ) {
        $this->orderHelper = $orderHelper;
        $this->refundHelper = $refundHelper;
        $configHelper->setConfig();
    }

    /**
     * @param array $data
     * @return array (keys: orderId, redirectUri, extOrderId)
     * @throws Client\Exception
     */
    public function orderCreate(array $data = [])
    {
        if (!$this->orderHelper->validateCreate($data)) {
            throw new Exception('Order request data array is invalid.');
        }
        $data = $this->orderHelper->addSpecialDataToOrder($data);
        $result = $this->orderHelper->create($data);
        if (!$result) {
            throw new Exception('There was a problem while processing order create request.');
        }
        return $result;
    }

    /**
     * @param string $payuplOrderId
     * @return string Transaction status
     * @throws Client\Exception
     */
    public function orderRetrieve($payuplOrderId)
    {
        if (!$this->orderHelper->validateRetrieve($payuplOrderId)) {
            throw new Exception('ID of order to retrieve is empty.');
        }
        $result = $this->orderHelper->retrieve($payuplOrderId);
        if (!$result) {
            throw new Exception('There was a problem while processing order retrieve request.');
        }
        return $result;
    }

    /**
     * @param string $payuplOrderId
     * @return bool|\OpenPayU_Result
     * @throws Client\Exception
     */
    public function orderCancel($payuplOrderId)
    {
        if (!$this->orderHelper->validateCancel($payuplOrderId)) {
            throw new Exception('ID of order to cancel is empty.');
        }
        $result = $this->orderHelper->cancel($payuplOrderId);
        if (!$result) {
            throw new Exception('There was a problem while processing order cancel request.');
        }
        return $result;
    }

    /**
     * @param array $data
     * @return true
     * @throws Client\Exception
     */
    public function orderStatusUpdate(array $data = [])
    {
        if (!$this->orderHelper->validateStatusUpdate($data)) {
            throw new Exception('Order status update request data array is invalid.');
        }
        $result = $this->orderHelper->statusUpdate($data);
        if (!$result) {
            throw new Exception('There was a problem while processing order status update request.');
        }
        return true;
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return array (keys: payuplOrderId, status, amount)
     * @throws Client\Exception
     */
    public function orderConsumeNotification(\Magento\Framework\App\Request\Http $request)
    {
        $result = $this->orderHelper->consumeNotification($request);
        if (!$result) {
            throw new Exception('There was a problem while consuming order notification.');
        }
        return $result;
    }

    /**
     * @param string $orderId
     * @param string $description
     * @param int $amount
     * @return true
     * @throws Client\Exception
     */
    public function refundCreate($orderId = '', $description = '', $amount = null)
    {
        if (!$this->refundHelper->validateCreate($orderId, $description, $amount)) {
            throw new Exception('Refund create request data is invalid.');
        }
        $result = $this->refundHelper->create($orderId, $description, $amount);
        if (!$result) {
            throw new Exception('There was a problem while processing refund create request.');
        }
        return true;
    }

    /**
     * @return Client\OrderInterface
     */
    public function getOrderHelper()
    {
        return $this->orderHelper;
    }

    /**
     * @return array|false
     */
    public function getPaytypes()
    {
        return $this->orderHelper->getPaytypes();
    }
}
