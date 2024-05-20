<?php

use Phalcon\Mvc\Model;

class InvoicePayments extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_invoice_payments');

        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('forma_pago', 'SatPaymentForms', 'id');
        $this->belongsTo('uso_cfdi', 'SatUsoCFDI', 'id');
        $this->belongsTo('invoice_id', 'Invoices', 'id');

    }
}
