<?php

use Phalcon\Mvc\Model;

class Currencies extends Model
{

    public function initialize ()
    {
        $this->setSource('acc_currency_types');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_bu', 'Users', 'id');

        $this->hasMany(
            'id',
            'Exchanges',
            'currency_id',
            [
                'foreignKey' => [
                    'message' => 'Hay tipos de cambio que dependen de esta moneda.',
                ]
            ]
        );
    }
}
