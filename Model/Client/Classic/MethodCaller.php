<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic;

class MethodCaller extends \Orba\Payupl\Model\Client\MethodCaller
{
    public function __construct(
        MethodCaller\Raw $rawMethod,
        \Orba\Payupl\Logger\Logger $logger
    )
    {
        parent::__construct(
            $rawMethod,
            $logger
        );
    }
}