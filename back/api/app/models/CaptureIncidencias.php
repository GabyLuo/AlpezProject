<?php

use Phalcon\Mvc\Model;

class CaptureIncidencias extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_incidencias');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('assistance_type', 'Incidencias', 'id');
        $this->belongsTo('employee_id', 'Employees', 'id');

    }
}
