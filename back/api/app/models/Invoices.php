<?php

use Phalcon\Mvc\Model;

class Invoices extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_invoices');

        $this->belongsTo('agent_id', 'Users', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('customer_branch_office_id', 'CustomerBranchOffices', 'id');
        $this->belongsTo('driver_id', 'Drivers', 'id');
        $this->belongsTo('in_bulk_movement_id', 'Movements', 'id');
        $this->belongsTo('bale_movement_id', 'Movements', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('shopping_cart_id', 'ShoppingCart', 'id');
        $this->belongsTo('forma_pago', 'SatPaymentForms', 'id');
        $this->belongsTo('uso_cfdi', 'SatUsoCFDI', 'id');
        $this->belongsTo('tax_company_id', 'CustomerTaxCompanies', 'id',[
            'alias' => 'CustomerTaxCompany',
        ]);

        $this->hasMany(
            'id',
            'InvoiceDetails',
            'invoice_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra paca que dependen de esta remisión.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceInBulkDetails',
            'invoice_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra abierta que dependen de esta remisión.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceLaminateDetails',
            'invoice_id',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra laminada que dependen de esta remisión.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoicePayments',
            'invoice_id',
            [
                'foreignKey' => [
                    'message' => 'Hay parcialidades que dependen de esta remisión.',
                ]
            ]
        );
    }
    
}
