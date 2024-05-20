<?php

use Phalcon\Mvc\Model;

class Timetables extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_timetables');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('position_id', 'Positions', 'id');
        $this->belongsTo('job_title_id', 'Shifts', 'id');

        /*$this->hasMany(
            'id',
            'Timetables',
            'position_id',
            [
                'foreignKey' => [
                    'message' => 'Hay Horarios que dependen de esta posición.',
                ]
            ]
        );*/
    }
}
