<?php
/**
 * @copyright Copyright (c) 2017 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;

/**
 * Container for all common and useful functions for console commands
 *
 * @package Orba\Payupl\Helper
 */
class Command extends AbstractHelper
{
    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $clientFactory;

    /**
     * @var \Orba\Payupl\Model\Sales\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Orba\Payupl\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Orba\Payupl\Model\Client\DataValidator
     */
    protected $dataValidator;

    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $resourceTransaction;

    /**
     * Command constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param \Orba\Payupl\Model\Sales\OrderFactory $salesOrderFactory
     * @param \Orba\Payupl\Model\OrderFactory $orderFactory
     * @param \Orba\Payupl\Model\Client\DataValidator $dataValidator
     * @param \Orba\Payupl\Model\ResourceModel\Transaction $resourceTransaction
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
        , \Orba\Payupl\Model\ClientFactory $clientFactory
        , \Orba\Payupl\Model\Sales\OrderFactory $salesOrderFactory
        , \Orba\Payupl\Model\OrderFactory $orderFactory
        , \Orba\Payupl\Model\Client\DataValidator $dataValidator
        , \Orba\Payupl\Model\ResourceModel\Transaction $resourceTransaction
    )
    {
        parent::__construct($context);
        $this->clientFactory = $clientFactory;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->orderFactory = $orderFactory;
        $this->dataValidator = $dataValidator;
        $this->resourceTransaction = $resourceTransaction;
    }

    /**
     * @return \Orba\Payupl\Model\Client
     */
    public function getClient()
    {
        return $this->clientFactory->create();
    }

    /**
     * @return \Orba\Payupl\Model\Sales\Order
     */
    public function getSalesOrder()
    {
        return $this->salesOrderFactory->create();
    }

    /**
     * @return \Orba\Payupl\Model\Order
     */
    public function getOrder()
    {
        return $this->orderFactory->create();
    }

    /**
     * Check order increment ID string validity for console command purposes
     * If not valid string error message returned
     *
     * @param string $orderIncrementId
     * @return bool|string
     */
    public function isInvalidIncrementOrderId($orderIncrementId)
    {
        if (empty($orderIncrementId)) {
            return "ID of order to retrieve is empty.";
        }
        return false;
    }

    /**
     * Check order object validity
     * If not valid string error message returned
     *
     * @param \Orba\Payupl\Model\Sales\Order $order
     * @return bool|string
     */
    public function isInvalidOrder($order)
    {
        if (!$order || !$order->getId()) {
            return "Order not found.";
        }
        return false;
    }

    /**
     * Check Payu.pl order ID validity
     * If not valid, string error message returned
     *
     * @param string $payuplOrderId
     * @return bool|string
     */
    public function isInvalidPayuplOrderId($payuplOrderId)
    {
        $client = $this->getClient();
        $orderHelper = $client->getOrderHelper();
        if (!$orderHelper->validateRetrieve($payuplOrderId)) {
            return "Payu.pl order ID '{$payuplOrderId}' is invalid";
        }
        return false;
    }

    /**
     * Check Payu.pl status validity
     * If not valid, string error message returned
     *
     * @param $status
     * @return bool
     */
    public function isInvalidPayuplStatus($status)
    {
        $client = $this->getClient();
        $orderHelper = $client->getOrderHelper();
        $description = $orderHelper->getStatusDescription($status);
        $invalid = empty($description);
        if ($invalid) {
            return "Invalid status code {$status}";
        }
        return false;
    }

    /**
     * Check amount validity
     * If not valid string error message returned
     *
     * @param $amount
     * @return bool|string
     */
    public function isInvalidAmount($amount)
    {
        if (false === filter_var($amount, FILTER_VALIDATE_FLOAT) ||
            !$this->dataValidator->validatePositiveFloat(intval($amount))
        ) {
            return "Amount must be decimal and bigger than 0";
        }
        return false;
    }

    /**
     * @param $orderIncrementId
     * @return string
     */
    public function getOrderIncrementId($orderIncrementId)
    {
        $orderIncrementId = trim($orderIncrementId);
        if ($errorMsg = $this->isInvalidIncrementOrderId($orderIncrementId)) {
            throw new \InvalidArgumentException($errorMsg);
        }
        return $orderIncrementId;
    }

    /**
     * @param $orderIncrementId
     * @return \Orba\Payupl\Model\Sales\Order
     */
    public function getOrderByOrderIncrementId($orderIncrementId)
    {
        $salesOrder = $this->getSalesOrder();
        $salesOrder->loadByIncrementId($orderIncrementId);
        if ($errorMsg = $this->isInvalidOrder($salesOrder)) {
            throw new NotFoundException(new Phrase($errorMsg));
        }
        return $salesOrder;
    }

    /**
     * @param string $payuplOrderId
     * @return string
     * @throws \InvalidArgumentException When Payu.pl order ID is invalid
     */
    public function getPayuplOrderId($payuplOrderId)
    {
        $payuplOrderId = trim($payuplOrderId);
        if ($errorMsg = $this->isInvalidPayuplOrderId($payuplOrderId)) {
            throw new \InvalidArgumentException($errorMsg);
        }
        return $payuplOrderId;
    }

    /**
     * @param $payuplOrderId
     * @return \Orba\Payupl\Model\Sales\Order
     * @throws NotFoundException When there is no corresponding order for Payu.pl order ID
     */
    public function getOrderByPayuplOrderId($payuplOrderId)
    {
        $order = $this->getOrder()->loadOrderByPayuplOrderId($payuplOrderId);
        if ($errorMsg = $this->isInvalidOrder($order)) {
            throw new NotFoundException(new Phrase($errorMsg));
        }
        return $order;
    }

    /**
     * Get Payu.pl status corresponding to API client type
     *
     * @param $status
     * @return string|int
     * @throws \InvalidArgumentException When Payu.pl status invalid
     */
    public function getStatus($status)
    {
        $status = trim($status);
        if ($errorMsg = $this->isInvalidPayuplStatus($status)) {
            $errorDescription = "You are using " . ($this->getClient() instanceof \Orba\Payupl\Model\Client\Rest ? "REST client" : "CLASSIC client") . PHP_EOL;
            $errorDescription .= "Available codes: " . PHP_EOL;
            foreach ($this->getClient()->getOrderHelper()->getAllStatuses() as $code => $description) {
                $errorDescription .= sprintf("%s (%s)", $code, $description) . PHP_EOL;
            }
            throw new \InvalidArgumentException($errorMsg . PHP_EOL . $errorDescription);
        }
        return $status;
    }

    /**
     * @param string $amount
     * @param \Orba\Payupl\Model\Sales\Order|null $order
     * @return float
     * @throws NotFoundException When for 'auto' amount given order object is invalid
     * @throws \InvalidArgumentException When amount value is invalid
     */
    public function getAmount($amount, $order = null)
    {
        $amount = trim($amount);
        if ('auto' == $amount) {
            // If 'auto' take amount from order
            if ($errorMsg = $this->isInvalidOrder($order)) {
                throw new NotFoundException(new Phrase($errorMsg));
            }
            /** @var \Orba\Payupl\Model\Sales\Order $order */
            $amount = $order->getGrandTotal();
        } else {
            if ($errorMsg = $this->isInvalidAmount($amount)) {
                throw new \InvalidArgumentException($errorMsg);
            }
        }
        return (float) $amount;
    }
}
