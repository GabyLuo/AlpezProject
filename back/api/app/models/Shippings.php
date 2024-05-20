<?php

use Phalcon\Mvc\Model;

class Shippings extends Model
{

    public function initialize ()
    {
        $this->setSource('log_shippings');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'ShippingDetails',
            'shipping_id',
            [
                'foreignKey' => [
                    'message' => 'Existen productos asignados a este envío.',
                ]
            ]
        );
    }
}
