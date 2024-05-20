<?php

use Phalcon\Mvc\Model;

class MovementDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_movement_details');

        $this->belongsTo('bag_id', 'ShipmentDetails', 'id');
        $this->belongsTo('bale_id', 'Bales', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('movement_id', 'Movements', 'id');
        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'BaleOpeningDetails',
            'exit_movement_detail_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de apertura de pacas que dependen de este movimiento.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BaleOpeningDetails',
            'entry_movement_detail_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de apertura de pacas que dependen de este movimiento.',
                ]
            ]
        );
    }
}
