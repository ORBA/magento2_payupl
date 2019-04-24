<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Magento\Framework\Exception\LocalizedException;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $clientFactory;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $orderHelper;

    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $session;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param \Orba\Payupl\Model\Order $orderHelper
     * @param \Orba\Payupl\Model\Session $session
     * @param \Orba\Payupl\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        \Orba\Payupl\Model\Order $orderHelper,
        \Orba\Payupl\Model\Session $session,
        \Orba\Payupl\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->clientFactory = $clientFactory;
        $this->orderHelper = $orderHelper;
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
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
        $orderId = $this->orderHelper->getOrderIdForPaymentStart();
        if ($orderId) {
            $order = $this->orderHelper->loadOrderById($orderId);
            if ($this->orderHelper->canStartFirstPayment($order)) {
                try {
                    $client = $this->clientFactory->create();
                    $clientOrderHelper = $client->getOrderHelper();
                    $orderData = $clientOrderHelper->getDataForOrderCreate($order);
                    $result = $client->orderCreate($orderData);
                    $this->orderHelper
                        ->addNewOrderTransaction($order, $result['orderId'], $clientOrderHelper->getNewStatus());
                    $this->orderHelper->setNewOrderStatus($order);
                    $redirectUrl = $result['redirectUri'];
                } catch (LocalizedException $e) {
                    $this->logger->critical($e);
                    $redirectUrl = 'orba_payupl/payment/end';
                    $redirectParams = ['exception' => '1'];
                }
                $this->session->setLastOrderId($orderId);
            }
        }
        $resultRedirect->setPath($redirectUrl, $redirectParams);
        return $resultRedirect;
    }
}
