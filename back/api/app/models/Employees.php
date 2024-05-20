<?php

use Phalcon\Mvc\Model;

class Employees extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_employees');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('positions_id', 'Positions', 'id');
        $this->belongsTo('shift_id', 'Shifts', 'id');
    }
}
