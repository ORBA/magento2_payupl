<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

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
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session\SuccessValidator $successValidator
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Orba\Payupl\Model\Session $session
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param \Orba\Payupl\Model\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Orba\Payupl\Model\Session $session,
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        \Orba\Payupl\Model\Order $orderHelper
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_successValidator = $successValidator;
        $this->_checkoutSession = $checkoutSession;
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
        try {
            if ($this->_successValidator->isValid()) {
                $clientOrderHelper = $this->_getClientOrderHelper();
                $this->_session->setLastOrderId(null);
                if (
                    $this->_orderHelper->paymentSuccessCheck() &&
                    $clientOrderHelper->paymentSuccessCheck()
                ) {
                    $redirectUrl = 'checkout/onepage/success';
                } else {
                    $redirectUrl = 'orba_payupl/payment/error';
                }
            } else if ($this->_session->getLastOrderId()) {
                $clientOrderHelper = $this->_getClientOrderHelper();
                if (
                    $this->_orderHelper->paymentSuccessCheck() &&
                    $clientOrderHelper->paymentSuccessCheck()
                ) {
                    $redirectUrl = 'orba_payupl/payment/repeat_success';
                } else {
                    $redirectUrl = 'orba_payupl/payment/repeat_error';
                }
            } else {
                throw new \Exception('Invalid checkout.');
            }
        } catch (\Exception $e) {
            $redirectUrl = '/';
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