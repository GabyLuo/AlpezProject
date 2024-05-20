<?php

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

class Supercluster extends BaseModel {

    public function initialize () {
        $this->setSource('sys_supercluster');
    }

    function validation() {
        $validator = new Validation();

        $validator->add(
            'code',
            new Uniqueness([
                   'message' => 'Ya existe un cluster con ese código.',
               ]
            )
        );

        $validator->add(
            'name',
            new Uniqueness([
                   'message' => 'Ya existe un cluster con ese nombre.',
               ]
            )
        );

        return $this->validate($validator);
    }
    
}