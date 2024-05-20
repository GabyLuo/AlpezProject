<?php

use Phalcon\Mvc\Model;

class Units extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_units');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'ProductionOrders',
            'unit_id',
            [
                'foreignKey' => [
                    'message' => 'Hay ordenes de producción que dependen de esta unidad.',
                ]
            ]
        );
    }
}
