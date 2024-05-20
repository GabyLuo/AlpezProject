<?php

use Phalcon\Mvc\Model;

class Vacations extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_vacation_grants');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        /*$this->hasMany(
            'id',
            'Areas',
            'department_id',
            [
                'foreignKey' => [
                    'message' => 'Hay areas que dependen de este departamento.',
                ]
            ]
        );*/
    }
}
