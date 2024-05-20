<?php

use Phalcon\Mvc\Model;

class Municipality extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_municipality');
    }
}
