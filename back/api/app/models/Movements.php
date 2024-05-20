<?php

use Phalcon\Mvc\Model;

class Movements extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_movements');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('storage_id', 'Storages', 'id');
        $this->belongsTo('transaction_id', 'Transactions', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'MovementDetails',
            'movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles que dependen de este movimiento.',
                ]
            ]
        );

        /*  $this->hasMany(
            'id',
            'ProductionLots',
            'movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes que dependen de este movimiento.',
                ]
            ]
        ); */

        /* $this->hasMany(
            'id',
            'ProductionLots',
            'raw_material_movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes que dependen de este movimiento.',
                ]
            ]
        ); */

        /* $this->hasMany(
            'id',
            'ProductionLots',
            'raw_material_return_movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes que dependen de este movimiento.',
                ]
            ]
        ); */

        /* $this->hasMany(
            'id',
            'Laminates',
            'additive_movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay laminados que dependen de este movimiento.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Laminates',
            'laminate_movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay laminados que dependen de este movimiento.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Laminates',
            'material_movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay laminados que dependen de este movimiento.',
                ]
            ]
        ); */

        /* $this->hasMany(
            'id',
            'RawMaterialShipments',
            'movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay recepciones que dependen de este movimiento.',
                ]
            ]
        ); */

        /* $this->hasMany(
            'id',
            'Shipments',
            'movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay recepciones que dependen de este movimiento.',
                ]
            ]
        ); */

        /* $this->hasMany(
            'id',
            'Invoices',
            'in_bulk_movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay remisiones que dependen de este movimiento.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Invoices',
            'bale_movement_id',
            [
                'foreignKey' => [
                    'message' => 'Hay remisiones que dependen de este movimiento.',
                ]
            ]
        ); */
    }
}
