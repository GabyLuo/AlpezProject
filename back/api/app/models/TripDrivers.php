<?php

use Phalcon\Mvc\Model;

class TripDrivers extends Model
{

    public function initialize ()
    {
        $this->setSource('log_trip_drivers');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->belongsTo(
            'driver_id',
            'Drivers',
            'id'
        );

        $this->belongsTo(
            'trip_id',
            'Trips',
            'id'
        );

    }
}
