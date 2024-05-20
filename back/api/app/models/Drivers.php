<?php

use Phalcon\Mvc\Model;

class Drivers extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_drivers');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Invoices',
            'driver_id',
            [
                'foreignKey' => [
                    'message' => 'Hay ventas registradas para este chofer.',
                ]
            ]
        );
    }
}
