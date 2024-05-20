<?php

use Phalcon\Mvc\Model;

class ExpensesType extends Model
{

    public function initialize ()
    {
        $this->setSource('log_expenses_type');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        /*$this->hasMany(
            'id',
            'Trips',
            'destiny_id',
            [
                'foreignKey' => [
                    'message' => 'Hay viajesque dependen de esta detino.',
                ]
            ]
        );*/
    }
}
