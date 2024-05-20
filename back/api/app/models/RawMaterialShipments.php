<?php

use Phalcon\Mvc\Model;

class RawMaterialShipments extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_raw_material_shipments');

        $this->belongsTo('movement_id', 'Movements', 'id');
        $this->belongsTo('supplier_id', 'Suppliers', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
