<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session\SuccessValidator
     */
    protected $_successValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * @var \Orba\Payupl\Model\ClientInterface
     */
    protected $_client;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session\SuccessValidator $successValidator
     * @param \Magento\Checkout\Model\Session $session
     * @param \Orba\Payupl\Model\ClientInterface $client
     * @param \Orba\Payupl\Model\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Magento\Checkout\Model\Session $session,
        \Orba\Payupl\Model\ClientInterface $client,
        \Orba\Payupl\Model\Order $orderHelper
    )
    {
        parent::__construct($context);
        $this->_successValidator = $successValidator;
        $this->_session = $session;
        $this->_client = $client;
        $this->_orderHelper = $orderHelper;
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
        if ($this->_successValidator->isValid()) {
            $orderId = $this->_session->getLastOrderId();
            $clientOrderHelper = $this->_client->getOrderHelper();
            $order = $this->_orderHelper->loadOrderById($orderId);
            $orderData = $clientOrderHelper->getDataForOrderCreate($order);
            $result = $this->_client->orderCreate($orderData);
            $this->_orderHelper->saveNewTransaction(
                $orderId,
                $result['orderId'],
                $result['extOrderId'],
                $clientOrderHelper->getNewStatus()
            );
            $this->_orderHelper->setNewOrderStatus($order);
            $redirectUrl = $result['redirectUri'];
        } else {
            $redirectUrl = 'checkout/cart';
        }
        $resultRedirect->setPath($redirectUrl);
        return $resultRedirect;
    }
}