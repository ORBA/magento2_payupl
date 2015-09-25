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
     * @var \Orba\Payupl\Model\Client
     */
    protected $_client;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_context;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session\SuccessValidator $successValidator
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Orba\Payupl\Model\Session $session
     * @param \Orba\Payupl\Model\ClientInterface $client
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Orba\Payupl\Model\Session $session,
        \Orba\Payupl\Model\ClientInterface $client
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_successValidator = $successValidator;
        $this->_checkoutSession = $checkoutSession;
        $this->_session = $session;
        $this->_client = $client;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /**
         * @var $orderHelper \Orba\Payupl\Model\Client\OrderInterface
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $orderHelper = $this->_client->getOrderHelper();
            if ($this->_successValidator->isValid()) {
                if ($orderHelper->paymentSuccessCheck($this->_context->getRequest())) {
                    $redirectUrl = 'checkout/onepage/success';
                } else {
                    $redirectUrl = 'orba_payupl/payment/error';
                }
            } else if ($this->_session->getLastOrderId()) {
                if ($orderHelper->paymentSuccessCheck($this->_context->getRequest())) {
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
}