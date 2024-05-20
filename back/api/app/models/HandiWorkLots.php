<?php

use Phalcon\Mvc\Model;

class HandiWorkLots extends Model
{

    public function initialize ()
    {
        $this->setSource('prd_lot_works');
        // $this->belongsTo('id', 'Lines', 'category_id');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
