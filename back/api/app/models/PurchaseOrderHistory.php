<?php

use Phalcon\Mvc\Model;

class PurchaseOrderHistory extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_order_history');

        $this->belongsTo('created_by', 'Users', 'id');
    }
}
