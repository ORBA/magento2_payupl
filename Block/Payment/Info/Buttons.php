<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Block\Payment\Info;

class Buttons extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'payment/info/buttons.phtml';

    public function getOrderId()
    {
        return $this->getParentBlock()->getInfo()->getOrder()->getId();
    }
}