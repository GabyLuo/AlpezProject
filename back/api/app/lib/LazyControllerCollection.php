<?php

use Phalcon\Mvc\Micro\Collection as MicroCollection;

class LazyControllerCollection extends MicroCollection
{
    public function __construct(string $controller, string $prefix)
    {
        $this->setHandler($controller, true);
        $this->setPrefix($prefix);
    }
}
