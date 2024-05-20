<?php

use Phalcon\Mvc\Model;

class Shifts extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_shifts');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Timetables',
            'job_title_id',
            [
                'foreignKey' => [
                    'message' => 'Hay horarios que dependen de este turno.',
                ]
            ]
        );
    }
}
