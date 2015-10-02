<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Sales\Order;

use Orba\Payupl\Model\Sales\Order;

class Config extends \Magento\Sales\Model\Order\Config
{
    const XML_PATH_ORDER_STATUS_NEW         = 'payment/orba_payupl/order_status_new';
    const XML_PATH_ORDER_STATUS_HOLDED      = 'payment/orba_payupl/order_status_holded';
    const XML_PATH_ORDER_STATUS_PROCESSING  = 'payment/orba_payupl/order_status_processing';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory,
        \Magento\Sales\Model\Resource\Order\Status\CollectionFactory $orderStatusCollectionFactory,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $orderStatusFactory,
            $orderStatusCollectionFactory,
            $state
        );
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Gets Payu.pl-specific default status for state.
     *
     * @param string $state
     * @return string
     */
    public function getStateDefaultStatus($state)
    {
        switch ($state) {
            case Order::STATE_PENDING_PAYMENT:
                return $this->_scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_NEW);
            case Order::STATE_HOLDED:
                return $this->_scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_HOLDED);
            case Order::STATE_PROCESSING:
                return $this->_scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_PROCESSING);
        }
        return parent::getStateDefaultStatus($state);
    }
}