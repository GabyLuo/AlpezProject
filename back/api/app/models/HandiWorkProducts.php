<?php

use Phalcon\Mvc\Model;

class HandiWorkProducts extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_handiwork_products');
        // $this->belongsTo('id', 'Lines', 'category_id');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
