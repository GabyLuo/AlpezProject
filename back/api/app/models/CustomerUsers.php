<?php

use Phalcon\Mvc\Model;

class CustomerUsers extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_customer_users');

        $this->belongsTo('user_id', 'Users', 'id');
        $this->belongsTo('customer_id', 'Customers', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
