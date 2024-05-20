<?php

use Phalcon\Mvc\Model;

class BaleOpenings extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_bale_openings');

        $this->belongsTo('transaction_id', 'Transactions', 'id');
        $this->belongsTo('operator_id', 'Operators', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'BaleOpeningDetails',
            'bale_opening_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles que dependen de esta apertura de paca.',
                ]
            ]
        );
    }
}
