<?php

use Phalcon\Mvc\Model;

class Lines extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_lines');

        $this->belongsTo('category_id', 'Categories', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Products',
            'line_id',
            [
                'foreignKey' => [
                    'message' => 'Hay productos que dependen de esta línea.',
                ]
            ]
        );
    }
}
