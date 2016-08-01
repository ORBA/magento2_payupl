<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment\Repeat;

use Magento\Framework\Exception\LocalizedException;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $session;

    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $clientFactory;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $orderHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Orba\Payupl\Model\Session $session
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param \Orba\Payupl\Model\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Orba\Payupl\Model\Session $session,
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        \Orba\Payupl\Model\Order $orderHelper
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->clientFactory = $clientFactory;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /**
         * @var $clientOrderHelper \Orba\Payupl\Model\Client\OrderInterface
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $orderId = $this->session->getLastOrderId();
        $redirectParams = [];
        if ($orderId) {
            try {
                $client = $this->clientFactory->create();
                $clientOrderHelper = $client->getOrderHelper();
                $order = $this->orderHelper->loadOrderById($orderId);
                $orderData = $clientOrderHelper->getDataForOrderCreate($order);
                $result = $client->orderCreate($orderData);
                $this->orderHelper->addNewOrderTransaction(
                    $order,
                    $result['orderId'],
                    $result['extOrderId'],
                    $clientOrderHelper->getNewStatus()
                );
                $this->orderHelper->setNewOrderStatus($order);
                $redirectUrl = $result['redirectUri'];
            } catch (LocalizedException $e) {
                $redirectUrl = 'orba_payupl/payment/end';
                $redirectParams = ['exception' => '1'];
            }
        } else {
            $redirectUrl = 'orba_payupl/payment/repeat_error';
        }
        $resultRedirect->setPath($redirectUrl, $redirectParams);
        return $resultRedirect;
    }
}
