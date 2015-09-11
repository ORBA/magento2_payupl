<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

class Sdk
{
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

    public function orderStatusUpdate(array $data)
    {
        return false;
    }
}