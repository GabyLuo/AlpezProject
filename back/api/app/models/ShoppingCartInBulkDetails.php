<?php

use Phalcon\Mvc\Model;

class ShoppingCartInBulkDetails extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_shopping_cart_in_bulk_details');

        $this->belongsTo('shopping_cart_id', 'ShoppingCart', 'id');
        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
