<?php

use Phalcon\Mvc\Model;

class PurchaseOrderDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_order_details');

        $this->belongsTo('po_id', 'PurchaseOrders', 'id');
        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
