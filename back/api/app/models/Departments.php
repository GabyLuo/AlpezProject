<?php

use Phalcon\Mvc\Model;

class Departments extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_departments');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Areas',
            'department_id',
            [
                'foreignKey' => [
                    'message' => 'Hay areas que dependen de este departamento.',
                ]
            ]
        );
    }
}
