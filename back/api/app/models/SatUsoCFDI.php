<?php
use Phalcon\Mvc\Model;

class SatUsoCFDI extends Model
{
    public function initialize ()
    {
        $this->setSource('sat_uso_cfdi');

        $this->hasMany(
            'id',
            'Invoices',
            'uso_cfdi',
            [
                'foreignKey' => [
                    'message' => 'El uso CFDI aun cuenta con remisiones relacionadas',
                ]
            ]
        );
    }
}