<?php

use Phalcon\Mvc\Model;

class Transactions extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_transactions');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Movements',
            'transaction_id',
            [
                'foreignKey' => [
                    'message' => 'Hay movimientos que dependen de esta transacción.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BaleOpenings',
            'transaction_id',
            [
                'foreignKey' => [
                    'message' => 'Hay aperturas de pacas que dependen de esta transacción.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BranchTransfers',
            'transaction_id',
            [
                'foreignKey' => [
                    'message' => 'Hay traspasos de sucursal que dependen de esta transacción.',
                ]
            ]
        );
    }
}
