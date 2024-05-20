<?php

use Phalcon\Mvc\Model;

class Areas extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_areas');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('department_id', 'Departments', 'id');

        $this->hasMany(
            'id',
            'Positions',
            'area_id',
            [
                'foreignKey' => [
                    'message' => 'Hay puestos que dependen de esta árrea.',
                ]
            ]
        );
    }
}
