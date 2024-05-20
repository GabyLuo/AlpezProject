<?php

use Phalcon\Mvc\Model;

class InvoiceDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_invoice_details');

        $this->belongsTo('invoice_id', 'Invoices', 'id');
        $this->belongsTo('bale_id', 'Bales', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
