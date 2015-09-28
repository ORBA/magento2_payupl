<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

use Orba\Payupl\Model\Client\Exception;
use Orba\Payupl\Model\ClientInterface;

class Rest implements ClientInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var OrderInterface
     */
    protected $_orderHelper;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Rest\Config $configHelper
     * @param Rest\Order $orderHelper
     * @param Rest\Refund $refundHelper
     * @throws Exception
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Rest\Config $configHelper,
        Rest\Order $orderHelper,
        Rest\Refund $refundHelper
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_orderHelper = $orderHelper;
        $this->_refundHelper = $refundHelper;
        $configHelper->setConfig();
    }

    /**
     * @inheritdoc
     */
    public function orderCreate(array $data = [])
    {
        if (!$this->_orderHelper->validateCreate($data)) {
            throw new Exception('Order request data array is invalid.');
        }
        $data = $this->_orderHelper->addSpecialData($data);
        $result = $this->_orderHelper->create($data);
        if (!$result) {
            throw new Exception('There was a problem while processing order create request.');
        }
        return $result;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
        return $result;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getOrderHelper()
    {
        return $this->_orderHelper;
    }
}