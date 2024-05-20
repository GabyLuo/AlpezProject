<?php

use Phalcon\Mvc\Model;

class ProductionOrders extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_orders');

        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('unit_id', 'Units', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'ProductionLots',
            'order_id',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes que dependen de esta orden de producción.',
                ]
            ]
        );
    }
}
