<?php

use Phalcon\Mvc\Model;

class CustomerBranchOffices extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_customer_branch_offices');

        $this->belongsTo('customer_id', 'Customers', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Invoices',
            'customer_branch_office_id',
            [
                'foreignKey' => [
                    'message' => 'Hay ventas registradas para esta sucursal de cliente.',
                ]
            ]
        );
    }
}
