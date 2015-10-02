<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Config\Source\Order;

class Status extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        if (isset($options[0]) && !$options[0]['value']) {
            unset($options[0]);
        }
        return $options;
    }
}
