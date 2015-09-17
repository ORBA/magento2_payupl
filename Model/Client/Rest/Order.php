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
    const STATUS_NEW = 'NEW';

    /**
     * @var Order\DataValidator
     */
    protected $_dataValidator;

    /**
     * @var Order\DataGetter
     */
    protected $_dataAdder;

    /**
     * @var MethodCaller
     */
    protected $_methodCaller;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Order\DataGetter
     */
    protected $_dataGetter;

    /**
     * @param \Orba\Payupl\Model\TransactionFactory $transactionFactory
     * @param Order\DataValidator $dataValidator
     * @param Order\DataGetter $dataAdder
     * @param \Orba\Payupl\Model\Client\Rest\MethodCaller $methodCaller
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param Order\DataGetter $dataGetter
     */
    public function __construct(
        \Orba\Payupl\Model\TransactionFactory $transactionFactory,
        Order\DataValidator $dataValidator,
        Order\DataGetter $dataAdder,
        MethodCaller $methodCaller,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Order\DataGetter $dataGetter
    )
    {
        parent::__construct($transactionFactory);
        $this->_dataValidator = $dataValidator;
        $this->_dataAdder = $dataAdder;
        $this->_methodCaller = $methodCaller;
        $this->_orderFactory = $orderFactory;
        $this->_dataGetter = $dataGetter;
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
    public function validateRetrieve($id)
    {
        return $this->_dataValidator->validateEmpty($id);
    }

    /**
     * @inheritdoc
     */
    public function validateCancel($id)
    {
        return $this->_dataValidator->validateEmpty($id);
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
            'continueUrl' => $this->_dataAdder->getContinueUrl(),
            'notifyUrl' => $this->_dataAdder->getNotifyUrl(),
            'customerIp' => $this->_dataAdder->getCustomerIp(),
            'merchantPosId' => $this->_dataAdder->getMerchantPosId()
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
    public function retrieve($id)
    {
        $result = $this->_methodCaller->call('orderRetrieve', [$id]);
        if ($result) {
            $response = $result->getResponse();
            return $response->status;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function cancel($id)
    {
        return (bool) ($this->_methodCaller->call('orderCancel', [$id]));
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
    public function getDataForOrderCreate($orderId)
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $this->_orderFactory->create();
        $order->load($orderId);
        if ($order->getId()) {
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
        } else {
            throw new Exception('Order with ID ' . $orderId . ' does not exist.');
        }
    }

    /**
     * @inheritdoc
     */
    public function getNewStatus()
    {
        return self::STATUS_NEW;
    }

}