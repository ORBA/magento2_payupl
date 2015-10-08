<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

use Orba\Payupl\Model\Client\Exception;

class Order implements \Orba\Payupl\Model\Client\OrderInterface
{
    const STATUS_PRE_NEW    = 0;
    const STATUS_NEW        = 1;

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

    public function __construct(
        \Magento\Framework\View\Context $context,
        Order\DataValidator $dataValidator,
        Order\DataGetter $dataGetter,
        \Orba\Payupl\Model\Session $session,
        \Magento\Framework\App\RequestInterface $request,
        \Orba\Payupl\Logger\Logger $logger
    )
    {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_dataValidator = $dataValidator;
        $this->_dataGetter = $dataGetter;
        $this->_session = $session;
        $this->_request = $request;
        $this->_logger = $logger;
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
        // TODO: Implement retrieve() method.
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
        // TODO: Implement consumeNotification() method.
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
        // TODO: Implement canProcessNotification() method.
    }

    /**
     * @inheritDoc
     */
    public function processNotification($payuplOrderId, $status, $amount)
    {
        // TODO: Implement processNotification() method.
    }
}