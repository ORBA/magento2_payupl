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
    protected $successValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $session;

    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $clientFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Orba\Payupl\Model\Order
     */
    protected $orderHelper;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $logger;

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
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->successValidator = $successValidator;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->clientFactory = $clientFactory;
        $this->orderHelper = $orderHelper;
        $this->logger = $logger;
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
            if ($this->successValidator->isValid()) {
                $redirectUrl = 'orba_payupl/payment/error';
                $this->session->setLastOrderId(null);
                $clientOrderHelper = $this->getClientOrderHelper();
                if ($this->orderHelper->paymentSuccessCheck() && $clientOrderHelper->paymentSuccessCheck()) {
                    $redirectUrl = 'checkout/onepage/success';
                }

            } else {
                if ($this->session->getLastOrderId()) {
                    $redirectUrl = 'orba_payupl/payment/repeat_error';
                    $clientOrderHelper = $this->getClientOrderHelper();
                    if ($this->orderHelper->paymentSuccessCheck() && $clientOrderHelper->paymentSuccessCheck()) {
                        $redirectUrl = 'orba_payupl/payment/repeat_success';
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
        $resultRedirect->setPath($redirectUrl);
        return $resultRedirect;
    }

    /**
     * @return \Orba\Payupl\Model\Client\OrderInterface
     */
    protected function getClientOrderHelper()
    {
        return $this->clientFactory->create()->getOrderHelper();
    }
}
