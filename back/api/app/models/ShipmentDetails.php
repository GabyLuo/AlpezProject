<?php

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
class ShipmentDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_shipment_details');

        $this->belongsTo('shipment_id', 'Shipments', 'id');
        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        // $this->hasMany(
        //     'id',
        //     'MovementDetails',
        //     'bag_id',
        //     [
        //         'foreignKey' => [
        //             'message' => 'Hay detalles de movimientos que dependen de este saco.',
        //         ]
        //     ]
        // );
    }

    function validation() {
        $validator = new Validation();

        $validator->add(
            ['shipment_id','product_id', 'product_shipment_number'],
            new Uniqueness([
                   'message' => 'Hay productos ya registrados.',
               ]
            )
        );

        return $this->validate($validator);
    }
}
