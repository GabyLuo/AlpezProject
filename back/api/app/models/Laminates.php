<?php

use Phalcon\Mvc\Model;

class Laminates extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_laminates');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('additive_movement_id', 'Movements', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('laminate_movement_id', 'Movements', 'id');
        $this->belongsTo('material_movement_id', 'Movements', 'id');
        $this->belongsTo('operator_id', 'Operators', 'id');
        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
