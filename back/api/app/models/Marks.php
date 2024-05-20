<?php

use Phalcon\Mvc\Model;

class Marks extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_marks');
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
