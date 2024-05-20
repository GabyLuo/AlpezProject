<?php

use Phalcon\Mvc\Model;

class Suburb extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_suburbs');
    }
}
