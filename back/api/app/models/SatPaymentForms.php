<?php
use Phalcon\Mvc\Model;

class SatPaymentForms extends Model
{
    public function initialize ()
    {
        $this->setSource('sat_formas_pagos');

        $this->hasMany(
            'id',
            'Invoices',
            'forma_pago',
            [
                'foreignKey' => [
                    'message' => 'La forma de pago aun cuenta con remisiones relacionadas',
                ]
            ]
        );
    }
}