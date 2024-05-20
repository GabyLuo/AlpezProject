<?php

use Phalcon\Mvc\Model;

class Positions extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_positions');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('area_id', 'Areas', 'id');

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
