<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\MethodCaller;

class PaytypesClient
{
    /**
     * @var \Orba\Payupl\Model\Client\Classic\Config
     */
    protected $configHelper;

    /**
     * @var \Zend\Http\Client
     */
    protected $client;

    /**
     * @param \Orba\Payupl\Model\Client\Classic\Config $configHelper
     */
    public function __construct(
        \Orba\Payupl\Model\Client\Classic\Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * @return \Zend\Http\Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $posId = $this->configHelper->getConfig('pos_id');
            $keyMd5 = $this->configHelper->getConfig('key_md5');
            $url = 'https://secure.payu.com/paygw/UTF/xml/' . $posId . '/' . substr($keyMd5, 0, 2) . '/paytype.xml';
            $this->client = new \Zend\Http\Client($url, ['adapter' => 'Zend\Http\Client\Adapter\Curl']);
        }
        return $this->client;
    }
}
