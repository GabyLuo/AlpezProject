<?php

use Phalcon\Mvc\Model;

class VacationsRequest extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_vacations_request');
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
