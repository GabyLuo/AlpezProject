<?php

use Phalcon\Mvc\Model;

class Samplings extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_samplings');

        $this->belongsTo('shipment_id', 'Shipments', 'id');
        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
