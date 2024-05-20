<?php

use Phalcon\Mvc\Model;

class Customers extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_customers');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'ProductionLots',
            'customer_id',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes que dependen de este cliente.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'CustomerBranchOffices',
            'customer_id',
            [
                'foreignKey' => [
                    'message' => 'Se deben eliminar primero las sucursales del cliente.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'CustomerUsers',
            'customer_id',
            [
                'foreignKey' => [
                    'message' => 'Hay usuarios que dependen de este cliente.',
                ]
            ]
        );
    }
}
