<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Payment;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var \Orba\Payupl\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var \Orba\Payupl\Model\ClientFactory
     */
    protected $clientFactory;

    /**
     * @var State
     */
    private $appState;

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
        State $appState,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->transactionResource = $transactionResource;
        $this->clientFactory = $clientFactory;
        $this->appState = $appState;
    }

    protected function _prepareLayout()
    {
        if ($this->appState->getAreaCode() === Area::AREA_FRONTEND) {
            $this->addChild('buttons', Info\Buttons::class);
        }
        parent::_prepareLayout();
    }

    protected function _prepareSpecificInformation($transport = null)
    {
        /**
         * @var $client \Orba\Payupl\Model\Client
         */
        $transport = parent::_prepareSpecificInformation($transport);
        $orderId = $this->getInfo()->getParentId();
        $status = $this->transactionResource->getLastStatusByOrderId($orderId);
        $client = $this->clientFactory->create();
        $statusDescription = $client->getOrderHelper()->getStatusDescription($status);
        $transport->setData((string) __('Status'), $statusDescription);
        return $transport;
    }
}
