<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client;

interface MethodCallerInterface
{
    /**
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    public function call($methodName, array $args = []);
}
