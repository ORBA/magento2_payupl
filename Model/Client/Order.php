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
     * @param Order\DataValidator $dataValidator
     * @param Order\DataAdder $dataAdder
     */
    public function __construct(
        \Orba\Payupl\Model\Client\Order\DataValidator $dataValidator,
        \Orba\Payupl\Model\Client\Order\DataAdder $dataAdder
    )
    {
        $this->_dataValidator = $dataValidator;
        $this->_dataAdder = $dataAdder;
    }

    public function validate(array $data = [])
    {
        return
            $this->_dataValidator->validateEmpty($data) &&
            $this->_dataValidator->validateBasicData($data) &&
            $this->_dataValidator->validateProductsData($data);
    }

    public function addSpecialData(array $data = [])
    {
        return array_merge($data, [
            'continueUrl' => $this->_dataAdder->getContinueUrl(),
            'notifyUrl' => $this->_dataAdder->getNotifyUrl(),
            'customerIp' => $this->_dataAdder->getCustomerIp(),
            'merchantPosId' => $this->_dataAdder->getMerchantPosId()
        ]);
    }
}