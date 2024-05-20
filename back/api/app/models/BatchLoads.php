<?php

use Phalcon\Mvc\Model;

class BatchLoads extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_batch_loads');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
