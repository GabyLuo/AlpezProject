<?php

use Phalcon\Mvc\Model;

class Files extends Model
{

    public function initialize ()
    {
        $this->setSource('dir_files');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
