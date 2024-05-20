<?php

use Phalcon\Mvc\Model;

class InvoicesFolios extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_invoices_folios');

        $this->belongsTo('forma_pago', 'SatPaymentForms', 'id');
        $this->belongsTo('uso_cfdi', 'SatUsoCFDI', 'id');
        $this->belongsTo('invoice_id', 'Invoices', 'id');

    }
}
