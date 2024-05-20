<?php

use Phalcon\Mvc\Model;

class Payments extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_payments');

    }
}
