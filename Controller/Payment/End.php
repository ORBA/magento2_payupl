<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Orba\Payupl\Model\Client\Exception;

class End extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session\SuccessValidator
     */
    protected $_successValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $_session;

    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $_clientFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_context;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session\SuccessValidator $successValidator
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Orba\Payupl\Model\Session $session
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param \Orba\Payupl\Model\Order $orderHelper
     * @param \Orba\Payupl\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Orba\Payupl\Model\Session $session,
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        \Orba\Payupl\Model\Order $orderHelper,
        \Orba\Payupl\Logger\Logger $logger
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_successValidator = $successValidator;
        $this->_checkoutSession = $checkoutSession;
        $this->_session = $session;
        $this->_clientFactory = $clientFactory;
        $this->_orderHelper = $orderHelper;
        $this->_logger = $logger;
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
        $redirectUrl = '/';
        try {
            if ($this->_successValidator->isValid()) {
                $redirectUrl = 'orba_payupl/payment/error';
                $this->_session->setLastOrderId(null);
                $clientOrderHelper = $this->_getClientOrderHelper();
                if (
                    $this->_orderHelper->paymentSuccessCheck() &&
                    $clientOrderHelper->paymentSuccessCheck()
                ) {
                    $redirectUrl = 'checkout/onepage/success';
                }

            } else if ($this->_session->getLastOrderId()) {
                $redirectUrl = 'orba_payupl/payment/repeat_error';
                $clientOrderHelper = $this->_getClientOrderHelper();
                if (
                    $this->_orderHelper->paymentSuccessCheck() &&
                    $clientOrderHelper->paymentSuccessCheck()
                ) {
                    $redirectUrl = 'orba_payupl/payment/repeat_success';
                }
            }
        } catch (Exception $e) {
            $this->_logger->critical($e);
        }
        $resultRedirect->setPath($redirectUrl);
        return $resultRedirect;
    }

    /**
     * @return \Orba\Payupl\Model\Client\OrderInterface
     */
    protected function _getClientOrderHelper()
    {
        return $this->_clientFactory->create()->getOrderHelper();
    }
}