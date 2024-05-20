<?php

use Phalcon\Mvc\Model;

class ProductionLots extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_lots');

        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('customer_id', 'Customers', 'id');
        $this->belongsTo('movement_id', 'Movements', 'id');
        $this->belongsTo('order_id', 'ProductionOrders', 'id');
        $this->belongsTo('raw_material_movement_id', 'Movements', 'id');
        $this->belongsTo('raw_material_return_movement_id', 'Movements', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Formulas',
            'lot_id',
            [
                'foreignKey' => [
                    'message' => 'Hay fórmulas que dependen de este lote.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionLotProcesses',
            'lot_id',
            [
                'foreignKey' => [
                    'message' => 'Hay procesos que dependen de este lote.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Formulas',
            'lot_id',
            [
                'foreignKey' => [
                    'message' => 'Hay formulas que dependen de este lote.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionMeasurements',
            'lot_id',
            [
                'foreignKey' => [
                    'message' => 'Hay mediciones que dependen de este lote.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionScraps',
            'lot_id',
            [
                'foreignKey' => [
                    'message' => 'Hay scraps que dependen de este lote.',
                ]
            ]
        );
    }
}
