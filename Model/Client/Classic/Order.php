<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

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
     * @var string[]
     */
    protected $statusDescription = [
        self::STATUS_PRE_NEW => 'New',
        self::STATUS_NEW => 'New',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_WAITING => 'Waiting for acceptance',
        self::STATUS_REJECTED_CANCELLED => 'Rejected',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_ERROR => 'Error'
    ];

    /**
     * @var Order\DataValidator
     */
    protected $dataValidator;

    /**
     * @var Order\DataGetter
     */
    protected $dataGetter;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $logger;

    /**
     * @var Order\Notification
     */
    protected $notificationHelper;

    /**
     * @var MethodCaller
     */
    protected $methodCaller;

    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var Order\Processor
     */
    protected $orderProcessor;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $rawResultFactory;

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
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->dataValidator = $dataValidator;
        $this->dataGetter = $dataGetter;
        $this->session = $session;
        $this->request = $request;
        $this->logger = $logger;
        $this->notificationHelper = $notificationHelper;
        $this->methodCaller = $methodCaller;
        $this->transactionResource = $transactionResource;
        $this->orderProcessor = $orderProcessor;
        $this->rawResultFactory = $rawResultFactory;
    }

    /**
     * @inheritDoc
     */
    public function validateCreate(array $data = [])
    {
        return
            $this->dataValidator->validateEmpty($data) &&
            $this->dataValidator->validateBasicData($data);
    }

    /**
     * @inheritDoc
     */
    public function validateRetrieve($payuplOrderId)
    {
        return $this->dataValidator->validateEmpty($payuplOrderId);
    }

    /**
     * @inheritDoc
     */
    public function validateCancel($payuplOrderId)
    {
        return $this->dataValidator->validateEmpty($payuplOrderId);
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
        $this->session->setOrderCreateData($data);
        return [
            'orderId' => md5($data['session_id']),
            'extOrderId' => $data['session_id'],
            'redirectUri' => $this->urlBuilder->getUrl('orba_payupl/classic/form')
        ];
    }

    /**
     * @inheritDoc
     */
    public function retrieve($payuplOrderId)
    {
        $posId = $this->dataGetter->getPosId();
        $ts = $this->dataGetter->getTs();
        $sig = $this->dataGetter->getSigForOrderRetrieve([
            'pos_id' => $posId,
            'session_id' => $payuplOrderId,
            'ts' => $ts
        ]);
        $result = $this->methodCaller->call('orderRetrieve', [
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
        $payuplOrderId = $this->notificationHelper->getPayuplOrderId($request);
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
        return $this->dataGetter->getBasicData($order);
    }

    /**
     * @inheritDoc
     */
    public function addSpecialDataToOrder(array $data = [])
    {
        $data['pos_id'] = $this->dataGetter->getPosId();
        $data['pos_auth_key'] = $this->dataGetter->getPosAuthKey();
        $data['client_ip'] = $this->dataGetter->getClientIp();
        $data['ts'] = $this->dataGetter->getTs();
        $data['sig'] = $this->dataGetter->getSigForOrderCreate($data);
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
        $errorCode = $this->request->getParam('error');
        if ($errorCode) {
            $extOrderId = $this->request->getParam('session_id');
            $this->logger->error('Payment error ' . $errorCode . ' for transaction ' . $extOrderId . '.');
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function canProcessNotification($payuplOrderId)
    {
        return !in_array(
            $this->transactionResource->getStatusByPayuplOrderId($payuplOrderId),
            [self::STATUS_COMPLETED, self::STATUS_CANCELLED]
        );
    }

    /**
     * @inheritDoc
     */
    public function processNotification($payuplOrderId, $status, $amount)
    {
        /**
         * @var $result \Magento\Framework\Controller\Result\Raw
         */
        $newest = $this->transactionResource->checkIfNewestByPayuplOrderId($payuplOrderId);
        $this->orderProcessor->processStatusChange($payuplOrderId, $status, $amount, $newest);
        $result = $this->rawResultFactory->create();
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
        return $this->methodCaller->call('getPaytypes');
    }

    /**
     * @inheritDoc
     */
    public function getStatusDescription($status)
    {
        if (isset($this->statusDescription[$status])) {
            return (string) __($this->statusDescription[$status]);
        }
        return false;
    }
}
