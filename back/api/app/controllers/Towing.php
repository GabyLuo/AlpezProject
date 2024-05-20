<?php

use Phalcon\Mvc\Model;

class Towing extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_towing_type');

    }
}
