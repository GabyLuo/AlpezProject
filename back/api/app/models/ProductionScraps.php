<?php

use Phalcon\Mvc\Model;

class ProductionScraps extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_scraps');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('lot_id', 'ProductionLots', 'id');
        $this->belongsTo('process_id', 'ProductionProcesses', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
