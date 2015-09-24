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
    const STATUS_WAITING    = 'WAITING FOR CONFIRMATION';
    const STATUS_CANCELED   = 'CANCELED';
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
     * @param \Orba\Payupl\Model\TransactionFactory $transactionFactory
     * @param \Orba\Payupl\Model\Resource\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Order\DataValidator $dataValidator
     * @param Order\DataGetter $dataGetter
     * @param \Orba\Payupl\Model\Client\Rest\MethodCaller $methodCaller
     */
    public function __construct(
        \Orba\Payupl\Model\TransactionFactory $transactionFactory,
        \Orba\Payupl\Model\Resource\Transaction\CollectionFactory $transactionCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Order\DataValidator $dataValidator,
        Order\DataGetter $dataGetter,
        MethodCaller $methodCaller
    )
    {
        parent::__construct(
            $transactionFactory,
            $transactionCollectionFactory,
            $orderFactory,
            $scopeConfig
        );
        $this->_dataValidator = $dataValidator;
        $this->_dataGetter = $dataGetter;
        $this->_methodCaller = $methodCaller;
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
     * @inheritdoc
     */
    public function validateConsumeNotification(array $data = [])
    {
        return $this->_dataValidator->validateEmpty($data);
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
    public function consumeNotification(array $data = [])
    {
        /**
         * @var $result \OpenPayU_Result
         */
        $result = $this->_methodCaller->call('orderConsumeNotification', [$data]);
        if ($result) {
            $response = $result->getResponse();
            return [
                'orderId' => $response->order->orderId,
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

}