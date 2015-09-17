<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Transaction model
 *
 * @method int getOrderId()
 * @method Transaction setOrderId(int)
 * @method string getPayuplOrderId()
 * @method Transaction setPayuplOrderId(string)
 * @method string getPayuplExternalOrderId()
 * @method Transaction setPayuplExternalOrderId(string)
 * @method int getTry()
 * @method Transaction setTry(int)
 * @method string getStatus()
 * @method Transaction setStatus(string)
 * @method string getCreatedAt()
 * @method Transaction setCreatedAt(string)
 */
class Transaction extends AbstractModel
{
    const FIELD_ID                          = 'transaction_id';
    const FIELD_ORDER_ID                    = 'order_id';
    const FIELD_PAYUPL_ORDER_ID             = 'payupl_order_id';
    const FIELD_PAYUPL_EXTERNAL_ORDER_ID    = 'payupl_external_order_id';
    const FIELD_TRY                         = 'try';
    const FIELD_STATUS                      = 'status';
    const FIELD_CREATED_AT                  = 'created_at';

    protected function _construct()
    {
        $this->_init(Resource\Transaction::class);
    }
}