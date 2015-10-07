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
    protected $_orderHelper;

    /**
     * @var Client\RefundInterface
     */
    protected $_refundHelper;

    /**
     * @param Client\ConfigInterface $configHelper
     * @param Client\OrderInterface $orderHelper
     * @param Client\RefundInterface $refundHelper
     */
    public function __construct(
        Client\ConfigInterface $configHelper,
        Client\OrderInterface $orderHelper,
        Client\RefundInterface $refundHelper
    )
    {
        $this->_orderHelper = $orderHelper;
        $this->_refundHelper = $refundHelper;
        $configHelper->setConfig();
    }

    /**
     * @param array $data
     * @return array (keys: orderId, redirectUri, extOrderId)
     * @throws Client\Exception
     */
    public function orderCreate(array $data = [])
    {
        if (!$this->_orderHelper->validateCreate($data)) {
            throw new Exception('Order request data array is invalid.');
        }
        $data = $this->_orderHelper->addSpecialDataToOrder($data);
        $result = $this->_orderHelper->create($data);
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
        if (!$this->_orderHelper->validateRetrieve($payuplOrderId)) {
            throw new Exception('ID of order to retrieve is empty.');
        }
        $result = $this->_orderHelper->retrieve($payuplOrderId);
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
        if (!$this->_orderHelper->validateCancel($payuplOrderId)) {
            throw new Exception('ID of order to cancel is empty.');
        }
        $result = $this->_orderHelper->cancel($payuplOrderId);
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
        if (!$this->_orderHelper->validateStatusUpdate($data)) {
            throw new Exception('Order status update request data array is invalid.');
        }
        $result = $this->_orderHelper->statusUpdate($data);
        if (!$result) {
            throw new Exception('There was a problem while processing order status update request.');
        }
        return true;
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return array (keys: payuplOrderId, status, amount')
     * @throws Client\Exception
     */
    public function orderConsumeNotification(\Magento\Framework\App\Request\Http $request)
    {
        $result = $this->_orderHelper->consumeNotification($request);
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
        if (!$this->_refundHelper->validateCreate($orderId, $description, $amount)) {
            throw new Exception('Refund create request data is invalid.');
        }
        $result = $this->_refundHelper->create($orderId, $description, $amount);
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
        return $this->_orderHelper;
    }
}