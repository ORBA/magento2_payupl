<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class Payupl extends AbstractMethod
{
    protected $_code = 'orba_payupl';

    protected $_isOffline = true;

    public function isAvailable($quote = null)
    {
        if (is_null($quote)) {
            return false;
        }
        return parent::isAvailable($quote);
    }
}