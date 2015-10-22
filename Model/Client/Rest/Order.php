<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Orba\Payupl\Model\Client\OrderInterface;
use Orba\Payupl\Model\Client\Rest\MethodCaller;
use Orba\Payupl\Model\Client\Exception;

class Order implements OrderInterface
{
    const STATUS_NEW        = 'NEW';
    const STATUS_PENDING    = 'PENDING';
    const STATUS_WAITING    = 'WAITING_FOR_CONFIRMATION';
    const STATUS_CANCELLED  = 'CANCELED';
    const STATUS_REJECTED   = 'REJECTED';
    const STATUS_COMPLETED  = 'COMPLETED';

    /**
     * @var Order\DataValidator
     */
    protected $_dataValidator;

    /**
     * @var Order\DataGetter
     */
    protected $_dataGetter;

    /**
     * @var MethodCaller
     */
    protected $_methodCaller;

    /**
     * @var Order\Processor
     */
    protected $_orderProcessor;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $_rawResultFactory;

    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $_transactionResource;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param Order\DataValidator $dataValidator
     * @param Order\DataGetter $dataGetter
     * @param \Orba\Payupl\Model\Client\Rest\MethodCaller $methodCaller
     * @param \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource
     * @param Order\Processor $orderProcessor
     * @param \Magento\Framework\Controller\Result\RawFactory $rawResultFactory
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Order\DataValidator $dataValidator,
        Order\DataGetter $dataGetter,
        MethodCaller $methodCaller,
        \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource,
        Order\Processor $orderProcessor,
        \Magento\Framework\Controller\Result\RawFactory $rawResultFactory,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->_dataValidator = $dataValidator;
        $this->_dataGetter = $dataGetter;
        $this->_methodCaller = $methodCaller;
        $this->_transactionResource = $transactionResource;
        $this->_orderProcessor = $orderProcessor;
        $this->_rawResultFactory = $rawResultFactory;
        $this->_request = $request;
    }

    /**
     * @inheritdoc
     */
    public function validateCreate(array $data = [])
    {
        return
            $this->_dataValidator->validateEmpty($data) &&
            $this->_dataValidator->validateBasicData($data) &&
            $this->_dataValidator->validateProductsData($data);
    }

    /**
     * @inheritdoc
     */
    public function validateRetrieve($payuplOrderId)
    {
        return $this->_dataValidator->validateEmpty($payuplOrderId);
    }

    /**
     * @inheritdoc
     */
    public function validateCancel($payuplOrderId)
    {
        return $this->_dataValidator->validateEmpty($payuplOrderId);
    }

    /**
     * @inheritdoc
     */
    public function validateStatusUpdate(array $data = [])
    {
        return
            $this->_dataValidator->validateEmpty($data) &&
            $this->_dataValidator->validateStatusUpdateData($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function addSpecialDataToOrder(array $data = [])
    {
        return array_merge($data, [
            'continueUrl' => $this->_dataGetter->getContinueUrl(),
            'notifyUrl' => $this->_dataGetter->getNotifyUrl(),
            'customerIp' => $this->_dataGetter->getCustomerIp(),
            'merchantPosId' => $this->_dataGetter->getMerchantPosId()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function create(array $data)
    {
        /**
         * @var $result \OpenPayU_Result
         */
        $response = $this->_methodCaller->call('orderCreate', [$data]);
        if ($response) {
            return [
                'orderId' => $response->orderId,
                'redirectUri' => $response->redirectUri,
                'extOrderId' => $data['extOrderId']
            ];
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function retrieve($payuplOrderId)
    {
        $response = $this->_methodCaller->call('orderRetrieve', [$payuplOrderId]);
        if ($response) {
            return [
                'status' => $response->orders[0]->status,
                'amount' => $response->orders[0]->totalAmount / 100
            ];
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function cancel($payuplOrderId)
    {
        return (bool) ($this->_methodCaller->call('orderCancel', [$payuplOrderId]));
    }

    /**
     * @inheritdoc
     */
    public function statusUpdate(array $data = [])
    {
        return (bool) ($this->_methodCaller->call('orderStatusUpdate', [$data]));
    }

    /**
     * @inheritdoc
     */
    public function consumeNotification(\Magento\Framework\App\Request\Http $request)
    {
        if (!$request->isPost()) {
            throw new \Orba\Payupl\Model\Client\Exception('POST request is required.');
        }
        $response = $this->_methodCaller->call('orderConsumeNotification', [$request->getContent()]);
        if ($response) {
            return [
                'payuplOrderId' => $response->order->orderId,
                'status' => $response->order->status,
                'amount' => (float) $response->order->totalAmount / 100
            ];
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getDataForOrderCreate(\Magento\Sales\Model\Order $order)
    {
        $data = ['products' => $this->_dataGetter->getProductsData($order)];
        $shippingData = $this->_dataGetter->getShippingData($order);
        if ($shippingData) {
            $data['products'][] = $shippingData;
        }
        $buyerData = $this->_dataGetter->getBuyerData($order);
        if ($buyerData) {
            $data['buyer'] = $buyerData;
        }
        $basicData = $this->_dataGetter->getBasicData($order);
        return array_merge($basicData, $data);
    }

    /**
     * @inheritdoc
     */
    public function getNewStatus()
    {
        return self::STATUS_NEW;
    }

    /**
     * @inheritdoc
     */
    public function paymentSuccessCheck()
    {
        return is_null($this->_request->getParam('error'));
    }

    /**
     * @inheritdoc
     */
    public function canProcessNotification($payuplOrderId)
    {
        return !in_array($this->_transactionResource->getStatusByPayuplOrderId($payuplOrderId), [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * @inheritdoc
     */
    public function processNotification($payuplOrderId, $status, $amount)
    {
        /**
         * @var $result \Magento\Framework\Controller\Result\Raw
         */
        $newest = $this->_transactionResource->checkIfNewestByPayuplOrderId($payuplOrderId);
        $this->_orderProcessor->processStatusChange($payuplOrderId, $status, $amount, $newest);
        $result = $this->_rawResultFactory->create();
        $result->setHttpResponseCode(200);
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getPaytypes()
    {
        return false;
    }
}