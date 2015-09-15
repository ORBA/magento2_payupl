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
     * @var \Orba\Payupl\Model\Order
     */
    protected $_orderHelper;

    /**
     * @var \Orba\Payupl\Model\Client
     */
    protected $_client;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session\SuccessValidator $successValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Magento\Checkout\Model\Session $session,
        \Orba\Payupl\Model\Order $orderHelper,
        \Orba\Payupl\Model\Client $client
    )
    {
        parent::__construct($context);
        $this->_successValidator = $successValidator;
        $this->_session = $session;
        $this->_orderHelper = $orderHelper;
        $this->_client = $client;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Orba\Payupl\Model\Client\Exception
     * @throws \Orba\Payupl\Model\Order\Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->_successValidator->isValid()) {
            $orderId = $this->_session->getLastOrderId();
            $orderData = $this->_orderHelper->getDataForNewTransaction($orderId);
            $clientResult = $this->_client->orderCreate($orderData);
            $clientResponse = $clientResult->getResponse();
            $redirectUrl = $clientResponse->redirectUri;
        } else {
            $redirectUrl = 'checkout/cart';
        }
        $resultRedirect->setPath($redirectUrl);
        return $resultRedirect;
    }
}