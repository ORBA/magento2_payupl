<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Resource\Transaction;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Orba\Payupl\Model\Transaction::class, \Orba\Payupl\Model\Resource\Transaction::class);
    }
}