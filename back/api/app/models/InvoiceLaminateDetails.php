<?php

use Phalcon\Mvc\Model;

class InvoiceLaminateDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_invoice_laminate_details');

        $this->belongsTo('invoice_id', 'Invoices', 'id');
        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
