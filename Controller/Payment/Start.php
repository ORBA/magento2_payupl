<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Orba\Payupl\Model\Client\Exception;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $_clientFactory;

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
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param \Orba\Payupl\Model\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        \Orba\Payupl\Model\Order $orderHelper,
        \Orba\Payupl\Model\Session $session
    )
    {
        parent::__construct($context);
        $this->_clientFactory = $clientFactory;
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
         * @var $resultRedirect \Magento\Framework\Controller\Result\Redirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectUrl = 'checkout/cart';
        $redirectParams = [];
        $orderId = $this->_orderHelper->getOrderIdForPaymentStart();
        if ($orderId) {
            $order = $this->_orderHelper->loadOrderById($orderId);
            if ($this->_orderHelper->canStartFirstPayment($order)) {
                try {
                    $client = $this->_clientFactory->create();
                    $clientOrderHelper = $client->getOrderHelper();
                    $orderData = $clientOrderHelper->getDataForOrderCreate($order);
                    $result = $client->orderCreate($orderData);
                    $this->_orderHelper->addNewOrderTransaction($order, $result['orderId'], $result['extOrderId'], $clientOrderHelper->getNewStatus());
                    $this->_orderHelper->setNewOrderStatus($order);
                    $redirectUrl = $result['redirectUri'];
                } catch (Exception $e) {
                    $redirectUrl = 'orba_payupl/payment/end';
                    $redirectParams = ['exception' => '1'];
                }
                $this->_session->setLastOrderId($orderId);
            }
        }
        $resultRedirect->setPath($redirectUrl, $redirectParams);
        return $resultRedirect;
    }
}