<?php

use Phalcon\Mvc\Model;

class SupplierDossiers extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_suppliers_dossiers');

        $this->belongsTo('supplier_id', 'Suppliers', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        // $this->hasMany(
        //     'id',
        //     'Invoices',
        //     'customer_branch_office_id',
        //     [
        //         'foreignKey' => [
        //             'message' => 'Hay ventas registradas para esta sucursal de cliente.',
        //         ]
        //     ]
        // );
    }
}
