<?php

use Phalcon\Mvc\Model;

class Equivalence extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_equivalence');

        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        /* $this->hasMany(
            'id',
            'MovementDetails',
            'bale_id',
            [
                'foreignKey' => [
                    'message' => 'Hay movimientos que dependen de esta paca.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceDetails',
            'bale_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de remisiones que dependen de esta paca.',
                ]
            ]
        ); */
    }
}
