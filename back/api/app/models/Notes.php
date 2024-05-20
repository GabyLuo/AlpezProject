<?php

use Phalcon\Mvc\Model;

class Notes extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_movement_notes');
        // $this->belongsTo('id', 'Lines', 'category_id');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
