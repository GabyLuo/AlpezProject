<?php

use Phalcon\Mvc\Model;

class PurchaseOrders extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_orders');

        $this->belongsTo('supplier_id', 'Suppliers', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'PurchaseOrderDetails',
            'po_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles que dependen de esta orden de compra.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Shipments',
            'order_id',
            [
                'foreignKey' => [
                    'message' => 'Hay recepciones que dependen de esta orden de compra.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'PurchaseOrderDocuments',
            'order_id',
            [
                'foreignKey' => [
                    'message' => 'Hay documentos que dependen de esta orden de compra.',
                ]
            ]
        );
    }
}
