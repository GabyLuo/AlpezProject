<?php

use Phalcon\Mvc\Model;

class ProductionLotProcesses extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_lot_processes');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('lot_id', 'ProductionLots', 'id');
        $this->belongsTo('process_id', 'ProductionProcesses', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
