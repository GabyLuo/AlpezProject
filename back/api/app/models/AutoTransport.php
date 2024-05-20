<?php

use Phalcon\Mvc\Model;

class AutoTransport extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_auto_transport');
    }
}
