<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Client
{
    /**
     * PayU.pl have REST and CLASSIC API
     */
    const TYPE_CLASSIC = 'classic';
    const TYPE_REST    = 'rest';

    /**
     * Define type of this client
     *
     * @var string
     */
    protected $clientType;

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
     * @throws LocalizedException
     */
    public function orderCreate(array $data = [])
    {
        if (!$this->orderHelper->validateCreate($data)) {
            throw new LocalizedException(new Phrase('Order request data array is invalid.'));
        }
        $data = $this->orderHelper->addSpecialDataToOrder($data);
        $result = $this->orderHelper->create($data);
        if (!$result) {
            throw new LocalizedException(new Phrase('There was a problem while processing order create request.'));
        }
        return $result;
    }

    /**
     * @param string $payuplOrderId
     * @return string Transaction status
     * @throws LocalizedException
     */
    public function orderRetrieve($payuplOrderId)
    {
        if (!$this->orderHelper->validateRetrieve($payuplOrderId)) {
            throw new LocalizedException(new Phrase('ID of order to retrieve is empty.'));
        }
        $result = $this->orderHelper->retrieve($payuplOrderId);
        if (!$result) {
            throw new LocalizedException(new Phrase('There was a problem while processing order retrieve request.'));
        }
        return $result;
    }

    /**
     * @param string $payuplOrderId
     * @return bool|\OpenPayU_Result
     * @throws LocalizedException
     */
    public function orderCancel($payuplOrderId)
    {
        if (!$this->orderHelper->validateCancel($payuplOrderId)) {
            throw new LocalizedException(new Phrase('ID of order to cancel is empty.'));
        }
        $result = $this->orderHelper->cancel($payuplOrderId);
        if (!$result) {
            throw new LocalizedException(new Phrase('There was a problem while processing order cancel request.'));
        }
        return $result;
    }

    /**
     * @param array $data
     * @return true
     * @throws LocalizedException
     */
    public function orderStatusUpdate(array $data = [])
    {
        if (!$this->orderHelper->validateStatusUpdate($data)) {
            throw new LocalizedException(new Phrase('Order status update request data array is invalid.'));
        }
        $result = $this->orderHelper->statusUpdate($data);
        if (!$result) {
            throw new LocalizedException(
                new Phrase('There was a problem while processing order status update request.')
            );
        }
        return true;
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return array (keys: payuplOrderId, status, amount)
     * @throws LocalizedException
     */
    public function orderConsumeNotification(\Magento\Framework\App\Request\Http $request)
    {
        $result = $this->orderHelper->consumeNotification($request);
        if (!$result) {
            throw new LocalizedException(new Phrase('There was a problem while consuming order notification.'));
        }
        return $result;
    }

    /**
     * @param string $orderId
     * @param string $description
     * @param int $amount
     * @return true
     * @throws LocalizedException
     */
    public function refundCreate($orderId = '', $description = '', $amount = null)
    {
        if (!$this->refundHelper->validateCreate($orderId, $description, $amount)) {
            throw new LocalizedException(new Phrase('Refund create request data is invalid.'));
        }
        $result = $this->refundHelper->create($orderId, $description, $amount);
        if (!$result) {
            throw new LocalizedException(new Phrase('There was a problem while processing refund create request.'));
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

    /**
     * Retrieve info about client type
     * return can by 'classic' or 'rest'
     *
     * @return string
     */
    public function getType()
    {
        return $this->clientType;
    }
}
