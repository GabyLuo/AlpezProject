<?php

use Phalcon\Mvc\Model;

class ShoppingCart extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_shopping_cart');

        $this->belongsTo('user_id', 'Users', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('branchoffice', 'BranchOffices', 'id',[
            'alias' => 'BranchOffice',
        ]);

        $this->hasMany(
            'id',
            'ShoppingCartBaleDetails',
            'shopping_cart_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra paca que dependen de este carrito.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShoppingCartInBulkDetails',
            'shopping_cart_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra abierta que dependen de este carrito.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShoppingCartLaminateDetails',
            'shopping_cart_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra laminada que dependen de este carrito.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Invoices',
            'shopping_cart_id',
            [
                'foreignKey' => [
                    'message' => 'Hay remisiones que dependen de este carrito.',
                ]
            ]
        );
    }
}
