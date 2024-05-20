<?php
use Phalcon\Mvc\Model;

class SatClaveProductos extends Model
{
    public function initialize ()
    {
        $this->setSource('sat_clave_productos');
    }
}