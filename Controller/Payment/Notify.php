<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Controller\Payment;

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
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Orba\Payupl\Model\ClientInterface $client
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    )
    {
        parent::__construct($context);
        $this->_context = $context;
        $this->_clientFactory = $clientFactory;
        $this->_resultForwardFactory = $resultForwardFactory;
    }

    public function execute()
    {
        $request = $this->_context->getRequest();
        $client = $this->_clientFactory->create();
        $response = $client->orderConsumeNotification($request);
        $clientOrderHelper = $client->getOrderHelper();
        if ($clientOrderHelper->canProcessNotification($response['payuplOrderId'])) {
            return $clientOrderHelper->processNotification($response['payuplOrderId'], $response['status'], $response['amount']);
        } else {
            /**
             * @var $resultForward \Magento\Framework\Controller\Result\Forward
             */
            $resultForward = $this->_resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}