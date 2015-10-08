<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

use Orba\Payupl\Model\Client\Exception;

class Notify extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_context;

    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $_clientFactory;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $_resultForwardFactory;

    /**
     * @var \Orba\Payupl\Logger\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Orba\Payupl\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Orba\Payupl\Logger\Logger $logger
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_clientFactory = $clientFactory;
        $this->_resultForwardFactory = $resultForwardFactory;
        $this->_logger = $logger;
    }

    public function execute()
    {
        $request = $this->_context->getRequest();
        try {
            $client = $this->_clientFactory->create();
            $response = $client->orderConsumeNotification($request);
            $clientOrderHelper = $client->getOrderHelper();
            if ($clientOrderHelper->canProcessNotification($response['payuplOrderId'])) {
                return $clientOrderHelper->processNotification($response['payuplOrderId'], $response['status'], $response['amount']);
            }
        } catch (Exception $e) {
            $this->_logger->critical($e);
        }
        /**
         * @var $resultForward \Magento\Framework\Controller\Result\Forward
         */
        $resultForward = $this->_resultForwardFactory->create();
        $resultForward->forward('noroute');
        return $resultForward;
    }
}