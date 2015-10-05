<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Orba\Payupl\Model\ClientInterface
     */
    protected $_client;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $_session;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Orba\Payupl\Model\ClientInterface $client
     * @param \Orba\Payupl\Model\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Orba\Payupl\Model\ClientInterface $client,
        \Orba\Payupl\Model\Order $orderHelper,
        \Orba\Payupl\Model\Session $session
    )
    {
        parent::__construct($context);
        $this->_client = $client;
        $this->_orderHelper = $orderHelper;
        $this->_session = $session;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Orba\Payupl\Model\Client\Exception
     */
    public function execute()
    {
        /**
         * @var $clientOrderHelper \Orba\Payupl\Model\Client\OrderInterface
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectUrl = 'checkout/cart';
        $orderId = $this->_orderHelper->getOrderIdForPaymentStart();
        if ($orderId) {
            $order = $this->_orderHelper->loadOrderById($orderId);
            if ($this->_orderHelper->canStartFirstPayment($order)) {
                $clientOrderHelper = $this->_client->getOrderHelper();
                $orderData = $clientOrderHelper->getDataForOrderCreate($order);
                $result = $this->_client->orderCreate($orderData);
                $this->_orderHelper->saveNewTransaction(
                    $orderId,
                    $result['orderId'],
                    $result['extOrderId'],
                    $clientOrderHelper->getNewStatus()
                );
                $this->_orderHelper->setNewOrderStatus($order);
                $this->_session->setLastOrderId($orderId);
                $redirectUrl = $result['redirectUri'];
            }
        }
        $resultRedirect->setPath($redirectUrl);
        return $resultRedirect;
    }
}