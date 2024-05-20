<?php

use Phalcon\Mvc\Model;

class Documents extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_documents');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
