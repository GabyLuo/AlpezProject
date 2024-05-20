<?php

use Phalcon\Mvc\Model;

class PostalCode extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_postal_code');
        $this->belongsTo('state_code', 'State', 'state_code',[
            'alias' => 'State',
        ]);
    }
}
