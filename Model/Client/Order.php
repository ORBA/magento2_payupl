<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

class Order
{
    /**
     * @var Order\DataValidator
     */
    protected $_dataValidator;

    /**
     * @var Order\DataAdder
     */
    protected $_dataAdder;

    /**
     * @var MethodCaller
     */
    protected $_methodCaller;

    /**
     * @param Order\DataValidator $dataValidator
     * @param Order\DataAdder $dataAdder
     * @param MethodCaller $methodCaller
     */
    public function __construct(
        Order\DataValidator $dataValidator,
        Order\DataAdder $dataAdder,
        MethodCaller $methodCaller
    )
    {
        $this->_dataValidator = $dataValidator;
        $this->_dataAdder = $dataAdder;
        $this->_methodCaller = $methodCaller;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateCreate(array $data = [])
    {
        return
            $this->_dataValidator->validateEmpty($data) &&
            $this->_dataValidator->validateBasicData($data) &&
            $this->_dataValidator->validateProductsData($data);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function validateRetrieve($id)
    {
        return $this->_dataValidator->validateEmpty($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function validateCancel($id)
    {
        return $this->_dataValidator->validateEmpty($id);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateStatusUpdate(array $data = [])
    {
        return
            $this->_dataValidator->validateEmpty($data) &&
            $this->_dataValidator->validateStatusUpdateData($data);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function validateConsumeNotification(array $data = [])
    {
        return $this->_dataValidator->validateEmpty($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function addSpecialData(array $data = [])
    {
        return array_merge($data, [
            'continueUrl' => $this->_dataAdder->getContinueUrl(),
            'notifyUrl' => $this->_dataAdder->getNotifyUrl(),
            'customerIp' => $this->_dataAdder->getCustomerIp(),
            'merchantPosId' => $this->_dataAdder->getMerchantPosId()
        ]);
    }

    /**
     * @param array $data
     * @return \OpenPayU_Result|bool
     */
    public function create(array $data)
    {
        return $this->_methodCaller->call('orderCreate', [$data]);
    }

    /**
     * @param string $id
     * @return bool|\OpenPayU_Result
     */
    public function retrieve($id)
    {
        return $this->_methodCaller->call('orderRetrieve', [$id]);
    }

    /**
     * @param string $id
     * @return bool|\OpenPayU_Result
     */
    public function cancel($id)
    {
        return $this->_methodCaller->call('orderCancel', [$id]);
    }

    /**
     * @param array $data
     * @return bool|\OpenPayU_Result
     */
    public function statusUpdate(array $data = [])
    {
        return $this->_methodCaller->call('orderStatusUpdate', [$data]);
    }

    /**
     * @param array $data
     * @return bool|\OpenPayU_Result
     */
    public function consumeNotification(array $data = [])
    {
        return $this->_methodCaller->call('orderConsumeNotification', [$data]);
    }

}