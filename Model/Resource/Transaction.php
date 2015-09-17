<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model\Resource;

use Magento\Framework\Model\Resource\Db\AbstractDb;
use Orba\Payupl\Model\Transaction as Model;

class Transaction extends AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime $date,
        $resourcePrefix = null
    )
    {
        parent::__construct(
            $context,
            $resourcePrefix
        );
        $this->_date = $date;
    }

    protected function _construct()
    {
        $this->_init('orba_payupl_transaction', Model::FIELD_ID);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * @var $object Model
         */
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->_date->formatDate(true));
        }
        return $this;
    }
}