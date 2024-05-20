<?php

use Phalcon\Mvc\Model;

class ProductionMeasurements extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_measurements');

        $this->belongsTo('lot_id', 'ProductionLots', 'id');
        $this->belongsTo('process_id', 'ProductionProcesses', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
