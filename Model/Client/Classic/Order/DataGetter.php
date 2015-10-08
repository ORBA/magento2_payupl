<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\Order;

class DataGetter
{
    /**
     * @var \Orba\Payupl\Model\Order\ExtOrderId
     */
    protected $_extOrderIdHelper;

    /**
     * @var \Orba\Payupl\Model\Client\Classic\Config
     */
    protected $_configHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @param \Orba\Payupl\Model\Order\ExtOrderId $extOrderIdHelper
     * @param \Orba\Payupl\Model\Client\Classic\Config $configHelper
     */
    public function __construct(
        \Orba\Payupl\Model\Order\ExtOrderId $extOrderIdHelper,
        \Orba\Payupl\Model\Client\Classic\Config $configHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    )
    {
        $this->_extOrderIdHelper = $extOrderIdHelper;
        $this->_configHelper = $configHelper;
        $this->_dateTime = $dateTime;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getBasicData(\Magento\Sales\Model\Order $order)
    {
        $incrementId = $order->getIncrementId();
        return [
            'amount' => $order->getGrandTotal() * 100,
            'desc' => __('Order # %1', [$incrementId]),
            'first_name' => $order->getCustomerFirstname(),
            'last_name' => $order->getCustomerLastname(),
            'email' => $order->getCustomerEmail(),
            'session_id' => $this->_extOrderIdHelper->generate($order),
            'order_id' => $incrementId
        ];
    }

    /**
     * @return string
     */
    public function getPosId()
    {
        return $this->_configHelper->getConfig('pos_id');
    }

    /**
     * @return string
     */
    public function getPosAuthKey()
    {
        return $this->_configHelper->getConfig('pos_auth_key');
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * @return int
     */
    public function getTs()
    {
        return $this->_dateTime->timestamp();
    }

    /**
     * @return string
     */
    public function getSigForOrderCreate(array $data = [])
    {
        return md5(
            $data['pos_id'] .
            $data['session_id'] .
            $data['pos_auth_key'] .
            $data['amount'] .
            $data['desc'] .
            $data['order_id'] .
            $data['first_name'] .
            $data['last_name'] .
            $data['email'] .
            $data['client_ip'] .
            $data['ts'] .
            $this->_configHelper->getConfig('key_md5')
        );
    }
}