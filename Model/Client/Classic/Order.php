<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Orba\Payupl\Model\Client\Exception;

class Order implements \Orba\Payupl\Model\Client\OrderInterface
{
    const STATUS_PRE_NEW            = 0;
    const STATUS_NEW                = 1;
    const STATUS_CANCELLED          = 2;
    const STATUS_REJECTED           = 3;
    const STATUS_PENDING            = 4;
    const STATUS_WAITING            = 5;
    const STATUS_REJECTED_CANCELLED = 7;
    const STATUS_COMPLETED          = 99;
    const STATUS_ERROR              = 888;

    /**
     * @var Order\DataValidator
     */
    protected $_dataValidator;

    /**
     * @var Order\DataGetter
     */
    protected $_dataGetter;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $_logger;

    /**
     * @var Order\Notification
     */
    protected $_notificationHelper;

    /**
     * @var MethodCaller
     */
    protected $_methodCaller;

    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $_transactionResource;

    /**
     * @var Order\Processor
     */
    protected $_orderProcessor;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $_rawResultFactory;

    /**
     * @param \Magento\Framework\View\Context $context
     * @param Order\DataValidator $dataValidator
     * @param Order\DataGetter $dataGetter
     * @param \Orba\Payupl\Model\Session $session
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Orba\Payupl\Logger\Logger $logger
     * @param Order\Notification $notificationHelper
     * @param MethodCaller $methodCaller
     * @param \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource
     * @param Order\Processor $orderProcessor
     * @param \Magento\Framework\Controller\Result\RawFactory $rawResultFactory
     */
    public function __construct(
        \Magento\Framework\View\Context $context,
        Order\DataValidator $dataValidator,
        Order\DataGetter $dataGetter,
        \Orba\Payupl\Model\Session $session,
        \Magento\Framework\App\RequestInterface $request,
        \Orba\Payupl\Logger\Logger $logger,
        Order\Notification $notificationHelper,
        MethodCaller $methodCaller,
        \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource,
        Order\Processor $orderProcessor,
        \Magento\Framework\Controller\Result\RawFactory $rawResultFactory
    )
    {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_dataValidator = $dataValidator;
        $this->_dataGetter = $dataGetter;
        $this->_session = $session;
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_notificationHelper = $notificationHelper;
        $this->_methodCaller = $methodCaller;
        $this->_transactionResource = $transactionResource;
        $this->_orderProcessor = $orderProcessor;
        $this->_rawResultFactory = $rawResultFactory;
    }

    /**
     * @inheritDoc
     */
    public function validateCreate(array $data = [])
    {
        return
            $this->_dataValidator->validateEmpty($data) &&
            $this->_dataValidator->validateBasicData($data);
    }

    /**
     * @inheritDoc
     */
    public function validateRetrieve($payuplOrderId)
    {
        return $this->_dataValidator->validateEmpty($payuplOrderId);
    }

    /**
     * @inheritDoc
     */
    public function validateCancel($payuplOrderId)
    {
        return $this->_dataValidator->validateEmpty($payuplOrderId);
    }

    /**
     * @inheritDoc
     */
    public function validateStatusUpdate(array $data = [])
    {
        // TODO: Implement validateStatusUpdate() method.
    }

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        $this->_session->setOrderCreateData($data);
        return [
            'orderId' => md5($data['session_id']),
            'extOrderId' => $data['session_id'],
            'redirectUri' => $this->_urlBuilder->getUrl('orba_payupl/classic/form')
        ];
    }

    /**
     * @inheritDoc
     */
    public function retrieve($payuplOrderId)
    {
        $posId = $this->_dataGetter->getPosId();
        $ts = $this->_dataGetter->getTs();
        $sig = $this->_dataGetter->getSigForOrderRetrieve([
            'pos_id' => $posId,
            'session_id' => $payuplOrderId,
            'ts' => $ts
        ]);
        $result = $this->_methodCaller->call('orderRetrieve', [
            $posId,
            $payuplOrderId,
            $ts,
            $sig
        ]);
        if ($result) {
            return [
                'status' => $result->transStatus,
                'amount' => $result->transAmount / 100
            ];
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function cancel($payuplOrderId)
    {
        // TODO: Implement cancel() method.
    }

    /**
     * @inheritDoc
     */
    public function statusUpdate(array $data = [])
    {
        // TODO: Implement statusUpdate() method.
    }

    /**
     * @inheritDoc
     */
    public function consumeNotification(\Magento\Framework\App\Request\Http $request)
    {
        $payuplOrderId = $this->_notificationHelper->getPayuplOrderId($request);
        $orderData = $this->retrieve($payuplOrderId);
        if ($orderData) {
            return [
                'payuplOrderId' => md5($payuplOrderId),
                'status' => $orderData['status'],
                'amount' => $orderData['amount']
            ];
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getDataForOrderCreate(\Magento\Sales\Model\Order $order)
    {
        return $this->_dataGetter->getBasicData($order);
    }

    /**
     * @inheritDoc
     */
    public function addSpecialDataToOrder(array $data = [])
    {
        $data['pos_id'] = $this->_dataGetter->getPosId();
        $data['pos_auth_key'] = $this->_dataGetter->getPosAuthKey();
        $data['client_ip'] = $this->_dataGetter->getClientIp();
        $data['ts'] = $this->_dataGetter->getTs();
        $data['sig'] = $this->_dataGetter->getSigForOrderCreate($data);
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getNewStatus()
    {
        return Order::STATUS_PRE_NEW;
    }

    /**
     * @inheritDoc
     */
    public function paymentSuccessCheck()
    {
        $errorCode = $this->_request->getParam('error');
        if ($errorCode) {
            $extOrderId = $this->_request->getParam('session_id');
            $this->_logger->error('Payment error ' . $errorCode . ' for transaction ' . $extOrderId . '.');
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canProcessNotification($payuplOrderId)
    {
        return !in_array($this->_transactionResource->getStatusByPayuplOrderId($payuplOrderId), [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * @inheritDoc
     */
    public function processNotification($payuplOrderId, $status, $amount)
    {
        /**
         * @var $result \Magento\Framework\Controller\Result\Raw
         */
        $newest = $this->_transactionResource->checkIfNewestByPayuplOrderId($payuplOrderId);
        $this->_orderProcessor->processStatusChange($payuplOrderId, $status, $amount, $newest);
        $result = $this->_rawResultFactory->create();
        $result
            ->setHttpResponseCode(200)
            ->setContents('OK');
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getPaytypes()
    {
        return $this->_methodCaller->call('getPaytypes');
    }
}