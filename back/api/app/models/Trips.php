<?php

use Phalcon\Mvc\Model;

class Trips extends Model
{

    public function initialize ()
    {
        $this->setSource('log_trips');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'TripExpenses',
            'trip_id',
            [
                'foreignKey' => [
                    'message' => 'Hay viajesque dependen de esta detino.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'TripDestinations',
            'trip_id',
            [
                'foreignKey' => [
                    'message' => 'Hay destinos dependen de este embarque.',
                ]
            ]
        );

        // $this->hasMany(
        //     'id',
        //     'TripDrivers',
        //     'trip_id'
        // );

        $this->hasManyToMany(
            'id',
            'TripDrivers',
            'trip_id', 'driver_id',
            'Drivers',
            'id'
        );
    }
}
