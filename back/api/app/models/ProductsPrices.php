<?php

use Phalcon\Mvc\Model;

class ProductsPrices extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_products_prices');

        $this->belongsTo('product_id', 'Products', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
