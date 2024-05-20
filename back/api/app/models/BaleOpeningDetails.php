<?php

use Phalcon\Mvc\Model;

class BaleOpeningDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_bale_opening_details');

        $this->belongsTo('bale_opening_id', 'BaleOpenings', 'id');
        $this->belongsTo('exit_movement_detail_id', 'MovementDetails', 'id');
        $this->belongsTo('entry_movement_detail_id', 'MovementDetails', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
