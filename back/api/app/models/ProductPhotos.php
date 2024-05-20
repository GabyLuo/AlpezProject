<?php

use Phalcon\Mvc\Model;

class ProductPhotos extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_product_photos');

        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('created_by', 'Users', 'id');

    }
}
