<?php

use Phalcon\Mvc\Model;

class OutputsTypes extends Model
{

    public function initialize ()
    {
        $this->setSource('fin_outputs_types');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'MovementsTrade',
            'output_id',
            [
                'foreignKey' => [
                    'message' => 'Hay movimientos que dependen de este rubro.',
                ]
            ]
        );
    }
}
