<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\MethodCaller\SoapClient;

class Refund extends \Zend\Soap\Client
{
    /**
     * @var int
     */
    protected $soapVersion = SOAP_1_1;
}