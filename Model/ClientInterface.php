<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Model;

interface ClientInterface
{
    public function orderCreate(array $data = []);

    public function orderRetrieve($id);

    public function orderCancel($id);

    public function orderStatusUpdate(array $data = []);

    public function orderConsumeNotification(array $data = []);

    public function refundCreate($orderId = '', $description = '', $amount = null);

    public function getOrderHelper();
}