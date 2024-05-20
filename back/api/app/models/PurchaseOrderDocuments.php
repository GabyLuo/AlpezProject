<?php

use Phalcon\Mvc\Model;

class PurchaseOrderDocuments extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_order_documents');

        $this->belongsTo('order_id', 'PurchaseOrders', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
