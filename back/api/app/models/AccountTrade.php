<?php

use Phalcon\Mvc\Model;

class AccountTrade extends Model
{

    public function initialize ()
    {
        $this->setSource('fin_accounts');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'MovementsTrade',
            'account_type_id',
            [
                'foreignKey' => [
                    'message' => 'Hay movimientos que dependen de esta cuenta.',
                ]
            ]
        );
    }
}
