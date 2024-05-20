<?php

use Phalcon\Mvc\Model;

class State extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_state');
    }
}
