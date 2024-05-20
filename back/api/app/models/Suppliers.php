<?php

use Phalcon\Mvc\Model;

class Suppliers extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_suppliers');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'PurchaseOrders',
            'supplier_id',
            [
                'foreignKey' => [
                    'message' => 'Hay ordenes de compra que dependen de este proveedor.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'RawMaterialShipments',
            'supplier_id',
            [
                'foreignKey' => [
                    'message' => 'Hay recepciones que dependen de este proveedor.',
                ]
            ]
        );
    }
}
