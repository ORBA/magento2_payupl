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
}