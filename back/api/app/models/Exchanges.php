<?php

use Phalcon\Mvc\Model;

class Exchanges extends Model
{

    public function initialize ()
    {
        $this->setSource('acc_exchange_types');

        $this->belongsTo('currency_id', 'Currencies', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        // $this->hasMany(
        //     'id',
        //     'Exchanges',
        //     'currency_id',
        //     [
        //         'foreignKey' => [
        //             'message' => 'Hay tipos de cambio que dependen de esta moneda.',
        //         ]
        //     ]
        // );
    }
}
