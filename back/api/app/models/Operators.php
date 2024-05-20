<?php

use Phalcon\Mvc\Model;

class Operators extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_operators');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Laminates',
            'operator_id',
            [
                'foreignKey' => [
                    'message' => 'Hay laminados que dependen de este operador.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BaleOpenings',
            'operator_id',
            [
                'foreignKey' => [
                    'message' => 'Hay aperturas de pacas que dependen de este operador.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BranchTransfers',
            'operator_id',
            [
                'foreignKey' => [
                    'message' => 'Hay traspasos de sucursal que dependen de este operador.',
                ]
            ]
        );
    }
}
