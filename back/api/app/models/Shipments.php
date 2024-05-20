<?php

use Phalcon\Mvc\Model;

class Shipments extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_shipments');

        $this->belongsTo('order_id', 'PurchaseOrders', 'id');
        $this->belongsTo('movement_id', 'Movements', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Samplings',
            'shipment_id',
            [
                'foreignKey' => [
                    'message' => 'Hay muestras que dependen de esta recepción.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShipmentDetails',
            'shipment_id',
            [
                'foreignKey' => [
                    'message' => 'Hay productos que dependen de esta recepción.',
                ]
            ]
        );
    }
}
