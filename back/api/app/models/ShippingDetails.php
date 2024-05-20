<?php

use Phalcon\Mvc\Model;

class ShippingDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('log_shippings_details');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

    }
}
