<?php

use Phalcon\Mvc\Model;

class Actions extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_actions');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
