<?php

use Phalcon\Mvc\Model;

class PurchaseOrderPayments extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_order_payments');

        // $this->belongsTo('supplier_id', 'Suppliers', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

    }
}
