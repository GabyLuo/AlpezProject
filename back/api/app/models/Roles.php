<?php

use Phalcon\Mvc\Model;

class Roles extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_roles');

        $this->belongsTo('parent_id', 'UserRoles', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'UserRoles',
            'role_id',
            [
                'foreignKey' => [
                    'message' => 'Hay usuarios con este rol asignado.',
                ]
            ]
        );
    }
}