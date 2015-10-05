<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Payment;

class Info extends \Magento\Payment\Block\Info
{
    protected function _prepareLayout()
    {
        $this->addChild('buttons', Info\Buttons::class);
        parent::_prepareLayout();
    }
}