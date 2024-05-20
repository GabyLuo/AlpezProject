<?php

use Phalcon\Mvc\Model;

class Formulas extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_lots_formulas');

        $this->belongsTo('lot_id', 'ProductionLots', 'id');
        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
