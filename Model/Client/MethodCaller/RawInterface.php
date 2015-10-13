<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\MethodCaller;

interface RawInterface
{
    /**
     * @param string $methodName
     * @param array $args
     * @return \stdClass
     * @throws \Orba\Payupl\Model\Client\Exception
     */
    public function call($methodName, array $args = []);
}