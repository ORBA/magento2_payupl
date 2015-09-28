<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest;

use Orba\Payupl\Model\Client\OrderInterface;
use Orba\Payupl\Model\Client\Rest\MethodCaller;
use Orba\Payupl\Model\Client\Exception;

class Order extends \Orba\Payupl\Model\Client\Order implements OrderInterface
{
    const STATUS_NEW        = 'NEW';
    const STATUS_PENDING    = 'PENDING';
    const STATUS_WAITING    = 'WAITING_FOR_CONFIRMATION';
    const STATUS_CANCELLED  = 'CANCELLED';
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
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    /**
     * @var Order\Processor
     */
    protected $_orderProcessor;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $_rawResultFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Order\DataValidator $dataValidator
     * @param Order\DataGetter $dataGetter
     * @param \Orba\Payupl\Model\Client\Rest\MethodCaller $methodCaller
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Order\DataValidator $dataValidator,
        Order\DataGetter $dataGetter,
        MethodCaller $methodCaller,
        \Orba\Payupl\Model\Order $orderHelper,
        Order\Processor $orderProcessor,
        \Magento\Framework\Controller\Result\RawFactory $rawResultFactory
    )
    {
        parent::__construct(
            $orderFactory,
            $scopeConfig
        );
        $this->_dataValidator = $dataValidator;
        $this->_dataGetter = $dataGetter;
        $this->_methodCaller = $methodCaller;
        $this->_orderHelper = $orderHelper;
        $this->_orderProcessor = $orderProcessor;
        $this->_rawResultFactory = $rawResultFactory;
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
    public function addSpecialData(array $data = [])
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
        $result = $this->_methodCaller->call('orderCreate', [$data]);
        if ($result) {
            $response = $result->getResponse();
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
        $result = $this->_methodCaller->call('orderRetrieve', [$payuplOrderId]);
        if ($result) {
            $response = $result->getResponse();
            return $response->orders[0]->status;
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
        /**
         * @var $result \OpenPayU_Result
         */
        $result = $this->_methodCaller->call('orderConsumeNotification', [$request]);
        if ($result) {
            $response = $result->getResponse();
            return [
                'payuplOrderId' => $response->order->orderId,
                'status' => $response->order->status
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
    public function paymentSuccessCheck(\Magento\Framework\App\RequestInterface $request)
    {
        return is_null($request->getParam('error'));
    }

    /**
     * @inheritdoc
     */
    public function canProcessNotification($payuplOrderId)
    {
        return !in_array($this->_orderHelper->getStatusByPayuplOrderId($payuplOrderId), [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * @inheritdoc
     */
    public function processNotification($payuplOrderId, $status)
    {
        /**
         * @var $result \Magento\Framework\Controller\Result\Raw
         */
        $orderId = $this->_orderHelper->getOrderIdByPayuplOrderId($payuplOrderId);
        if (!$orderId) {
            throw new Exception('Order not found.');
        }
        $newest = $this->_orderHelper->checkIfNewestByPayuplOrderId($payuplOrderId);
        $this->_orderProcessor->processStatusChange($orderId, $status, $newest);
        $result = $this->_rawResultFactory->create();
        $result->setHttpResponseCode(200);
        return $result;
    }

}