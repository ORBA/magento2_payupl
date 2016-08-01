<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Rest\MethodCaller;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Orba\Payupl\Model\Client\MethodCaller\RawInterface;

class Raw implements RawInterface
{
    /**
     * @param string $methodName
     * @param array $args
     * @return \stdClass
     * @throws LocalizedException
     */
    public function call($methodName, array $args = [])
    {
        $result = call_user_func_array([$this, $methodName], $args);
        return $this->getResponse($result);
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
     * @param string $data
     * @return \OpenPayU_Result
     * @throws \OpenPayU_Exception
     */
    public function orderConsumeNotification($data)
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

    /**
     * @param \OpenPayU_Result $result
     * @return \stdClass
     * @throws LocalizedException
     */
    protected function getResponse($result)
    {
        $response = $result->getResponse();
        if (isset($response->status)) {
            $status = $response->status;
            if ((string)$status->statusCode !== 'SUCCESS') {
                throw new LocalizedException(new Phrase(\Zend_Json::encode($status)));
            }
        }
        return $response;
    }
}
