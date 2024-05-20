<?php

use Phalcon\Mvc\Model;

class TripDestinations extends Model
{

    public function initialize ()
    {
        $this->setSource('log_trip_destinations');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('postal_code_id', 'PostalCode', 'id',[
            'alias' => 'PostalCode',
        ]);
        $this->belongsTo('suburb_id', 'Suburb', 'id',[
            'alias' => 'Suburb',
        ]);

    }
}
