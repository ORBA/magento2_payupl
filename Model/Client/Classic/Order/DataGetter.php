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
    protected $extOrderIdHelper;

    /**
     * @var \Orba\Payupl\Model\Client\Classic\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Orba\Payupl\Model\Session
     */
    protected $session;

    /**
     * @param \Orba\Payupl\Model\Order\ExtOrderId $extOrderIdHelper
     * @param \Orba\Payupl\Model\Client\Classic\Config $configHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Orba\Payupl\Model\Session $session
     */
    public function __construct(
        \Orba\Payupl\Model\Order\ExtOrderId $extOrderIdHelper,
        \Orba\Payupl\Model\Client\Classic\Config $configHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Orba\Payupl\Model\Session $session
    ) {
        $this->extOrderIdHelper = $extOrderIdHelper;
        $this->configHelper = $configHelper;
        $this->dateTime = $dateTime;
        $this->session = $session;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getBasicData(\Magento\Sales\Model\Order $order)
    {
        $incrementId = $order->getIncrementId();
        $billingAddress = $order->getBillingAddress();
        $data = [
            'amount' => $order->getGrandTotal() * 100,
            'desc' => __('Order # %1', [$incrementId]),
            'first_name' => $billingAddress->getFirstname(),
            'last_name' => $billingAddress->getLastname(),
            'email' => $order->getCustomerEmail(),
            'session_id' => $this->extOrderIdHelper->generate($order),
            'order_id' => $incrementId
        ];
        $paytype = $this->session->getPaytype();
        if ($paytype) {
            $data['pay_type'] = $paytype;
            $this->session->setPaytype(null);
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getPosId()
    {
        return $this->configHelper->getConfig('pos_id');
    }

    /**
     * @return string
     */
    public function getPosAuthKey()
    {
        return $this->configHelper->getConfig('pos_auth_key');
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
        return $this->dateTime->timestamp();
    }

    /**
     * @param array $data
     * @return string
     */
    public function getSigForOrderCreate(array $data = [])
    {
        return md5(
            $data['pos_id'] .
            (isset($data['pay_type']) ? $data['pay_type'] : '') .
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
            $this->configHelper->getConfig('key_md5')
        );
    }

    /**
     * @param array $data
     * @return string
     */
    public function getSigForOrderRetrieve(array $data = [])
    {
        return md5(
            $data['pos_id'] .
            $data['session_id'] .
            $data['ts'] .
            $this->configHelper->getConfig('key_md5')
        );
    }
}
