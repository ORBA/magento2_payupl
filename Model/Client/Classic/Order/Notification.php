<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Client\Classic\Order;

use Orba\Payupl\Model\Client\Exception;

class Notification
{
    /**
     * @var \Orba\Payupl\Model\Client\Classic\Config
     */
    protected $configHelper;

    /**
     * @param \Orba\Payupl\Model\Client\Classic\Config $configHelper
     */
    public function __construct(
        \Orba\Payupl\Model\Client\Classic\Config $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    public function getPayuplOrderId($request)
    {
        if (!$request->isPost()) {
            throw new \Orba\Payupl\Model\Client\Exception('POST request is required.');
        }
        $sig = $request->getParam('sig');
        $ts = $request->getParam('ts');
        $posId = $request->getParam('pos_id');
        $sessionId = $request->getParam('session_id');
        $secondKeyMd5 = $this->configHelper->getConfig('second_key_md5');
        if (md5($posId . $sessionId . $ts . $secondKeyMd5) === $sig) {
            return $sessionId;
        }
        throw new \Orba\Payupl\Model\Client\Exception('Invalid SIG.');
    }
}
