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
    protected $_session;

    /**
     * @var \Orba\Payupl\Model\Client
     */
    protected $_client;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session\SuccessValidator $successValidator
     * @param \Magento\Checkout\Model\Session $session
     * @param \Orba\Payupl\Model\ClientInterface $client
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Magento\Checkout\Model\Session $session,
        \Orba\Payupl\Model\ClientInterface $client
    )
    {
        parent::__construct($context);
        $this->_successValidator = $successValidator;
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
            if (!$this->_successValidator->isValid()) {
                throw new \Exception('Invalid checkout.');
            }
            $orderId = $this->_session->getLastOrderId();
            $orderHelper = $this->_client->getOrderHelper();
            $payuplOrderId = $orderHelper->getLastPayuplOrderIdByOrderId($orderId);
            if (!$payuplOrderId) {
                throw new \Exception('Could not get Payu.pl order ID.');
            }
            $status = $this->_client->orderRetrieve($payuplOrderId);
            if ($orderHelper->canContinueCheckout($status)) {
                $redirectUrl = 'checkout/onepage/success';
            }
        } catch (\Exception $e) {
            $redirectUrl = 'checkout/cart';
        }
        $resultRedirect->setPath($redirectUrl);
        return $resultRedirect;
    }
}