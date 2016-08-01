<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\MethodCaller;

use Magento\Framework\Exception\LocalizedException;

interface RawInterface
{
    /**
     * @param string $methodName
     * @param array $args
     * @return \stdClass
     * @throws LocalizedException
     */
    public function call($methodName, array $args = []);
}
