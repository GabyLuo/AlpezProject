<?php

use Phalcon\Mvc\Model;

class Incidencias extends Model
{

    public function initialize ()
    {
        $this->setSource('hrs_incidencia_type');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'CaptureIncidencias',
            'assistance_type',
            [
                'foreignKey' => [
                    'message' => 'Hay capturas que dependen de esta incidencia.',
                ]
            ]
        );
    }
}
