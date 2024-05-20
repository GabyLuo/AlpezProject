<?php

use Phalcon\Mvc\Model;

class Channels extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_channels');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        // $this->hasMany(
        //     'id',
        //     'Lines',
        //     'category_id',
        //     [
        //         'foreignKey' => [
        //             'message' => 'Hay líneas que dependen de esta categoría.',
        //         ]
        //     ]
        // );
    }
}
