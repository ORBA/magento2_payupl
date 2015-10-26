<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Payment;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $_transactionResource;

    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $_clientFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource
     * @param \Orba\Payupl\Model\ClientFactory $clientFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Orba\Payupl\Model\ResourceModel\Transaction $transactionResource,
        \Orba\Payupl\Model\ClientFactory $clientFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_transactionResource = $transactionResource;
        $this->_clientFactory = $clientFactory;
    }

    protected function _prepareLayout()
    {
        $this->addChild('buttons', Info\Buttons::class);
        parent::_prepareLayout();
    }

    protected function _prepareSpecificInformation($transport = null)
    {
        /**
         * @var $client \Orba\Payupl\Model\Client
         */
        $transport = parent::_prepareSpecificInformation($transport);
        $orderId = $this->getInfo()->getParentId();
        $status = $this->_transactionResource->getLastStatusByOrderId($orderId);
        $client = $this->_clientFactory->create();
        $statusDescription = $client->getOrderHelper()->getStatusDescription($status);
        $transport->setData((string) __('Status'), $statusDescription);
        return $transport;
    }
}