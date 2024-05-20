<?php

use Phalcon\Mvc\Model;

class Categories extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_categories');
        // $this->belongsTo('id', 'Lines', 'category_id');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Lines',
            'category_id',
            [
                'foreignKey' => [
                    'message' => 'Hay líneas que dependen de esta categoría.',
                ]
            ]
        );
    }
}
