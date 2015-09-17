<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\MethodCaller;

class Raw
{
    /**
     * @param string $methodName
     * @param array $args
     * @return \OpenPayU_Result
     * @throws \OpenPayU_Exception
     */
    public function call($methodName, array $args = [])
    {
        return call_user_func_array([$this, $methodName], $args);
    }

    /**
     * @param array $data
     * @return \OpenPayU_Result
     * @throws \OpenPayU_Exception
     */
    public function orderCreate(array $data)
    {
        return \OpenPayU_Order::create($data);
    }

    /**
     * @param string $id
     * @return \OpenPayU_Result
     * @throws \OpenPayU_Exception
     */
    public function orderRetrieve($id)
    {
        return \OpenPayU_Order::retrieve($id);
    }

    /**
     * @param string $id
     * @return \OpenPayU_Result
     * @throws \OpenPayU_Exception
     */
    public function orderCancel($id)
    {
        return \OpenPayU_Order::cancel($id);
    }

    /**
     * @param array $data
     * @return \OpenPayU_Result
     * @throws \OpenPayU_Exception
     */
    public function orderStatusUpdate(array $data)
    {
        return \OpenPayU_Order::statusUpdate($data);
    }

    /**
     * @param array $data
     * @return \OpenPayU_Result
     * @throws \OpenPayU_Exception
     */
    public function orderConsumeNotification(array $data)
    {
        return \OpenPayU_Order::consumeNotification($data);
    }

    /**
     * @param string $orderId
     * @param string $description
     * @param null|int $amount
     * @return \OpenPayU_Result
     * @throws \OpenPayU_Exception
     */
    public function refundCreate($orderId, $description, $amount = null)
    {
        return \OpenPayU_Refund::create($orderId, $description, $amount);
    }
}