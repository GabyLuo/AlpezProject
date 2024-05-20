<?php

use Phalcon\Mvc\Model;

class BatchLoadsDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_batch_load_details');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
