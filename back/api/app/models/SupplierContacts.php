<?php

use Phalcon\Mvc\Model;

class SupplierContacts extends Model
{

    public function initialize ()
    {
        $this->setSource('pur_supplier_contacts');

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
