<?php

use Phalcon\Mvc\Model;

class Vehicle extends Model
{

    public function initialize ()
    {
        $this->setSource('log_vehicle');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('vehicle_config', 'AutoTransport', 'id',[
            'alias' => 'AutoTransport',
        ]);
        $this->belongsTo('towing_type_id', 'Towing', 'id',[
            'alias' => 'Towing',
        ]);

        $this->hasMany(
            'id',
            'Trips',
            'economic_number',
            [
                'foreignKey' => [
                    'message' => 'Hay embarques dependen de esta vehículo.',
                ]
            ]
        );
    }
}
