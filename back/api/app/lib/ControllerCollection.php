<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

class ControllerCollection extends MicroCollection
{
    public function __construct(Controller $controller, string $prefix)
    {
        $this->setHandler($controller);
        $this->setPrefix($prefix);
    }
}