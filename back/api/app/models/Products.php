<?php

use Phalcon\Mvc\Model;

class Products extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_products');

        $this->belongsTo('family_id', 'Products', 'id');
        $this->belongsTo('line_id', 'Lines', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('created_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Products',
            'family_id',
            [
                'foreignKey' => [
                    'message' => 'Hay otros productos que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionOrders',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay ordenes de producción que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Formulas',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay fórmulas que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionLots',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'MovementDetails',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay movimientos que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'PurchaseOrderDetails',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay ordenes de compra que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShipmentDetails',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay jumbos que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Samplings',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay muestras que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Laminates',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay laminados que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceInBulkDetails',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra abierta de remisiones que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Bales',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay pacas que dependen de este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductsPrices',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'Hay precios registrados para este producto.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShoppingCartBaleDetails',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'El producto se encuentra registrado en algunos carritos de compra.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShoppingCartInBulkDetails',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'El producto se encuentra registrado en algunos carritos de compra.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShoppingCartLaminateDetails',
            'product_id',
            [
                'foreignKey' => [
                    'message' => 'El producto se encuentra registrado en algunos carritos de compra.',
                ]
            ]
        );
    }
}
