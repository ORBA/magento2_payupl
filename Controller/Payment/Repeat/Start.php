<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment\Repeat;

use Orba\Payupl\Model\Client\Exception;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $_session;

    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $_clientFactory;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

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
    )
    {
        parent::__construct($context);
        $this->_session = $session;
        $this->_clientFactory = $clientFactory;
        $this->_orderHelper = $orderHelper;
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
        $orderId = $this->_session->getLastOrderId();
        $redirectParams = [];
        if ($orderId) {
            try {
                $client = $this->_clientFactory->create();
                $clientOrderHelper = $client->getOrderHelper();
                $order = $this->_orderHelper->loadOrderById($orderId);
                $orderData = $clientOrderHelper->getDataForOrderCreate($order);
                $result = $client->orderCreate($orderData);
                $this->_orderHelper->addNewOrderTransaction(
                    $order,
                    $result['orderId'],
                    $result['extOrderId'],
                    $clientOrderHelper->getNewStatus()
                );
                $this->_orderHelper->setNewOrderStatus($order);
                $redirectUrl = $result['redirectUri'];
            } catch (Exception $e) {
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