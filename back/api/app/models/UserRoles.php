<?php

use Phalcon\Mvc\Model;

class UserRoles extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_user_roles');

        $this->belongsTo('role_id', 'Roles', 'id');
        $this->belongsTo('user_id', 'Users', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}