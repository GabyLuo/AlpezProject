<?php

use Phalcon\Mvc\Model;

class StorageTypes extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_storage_types');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Storages',
            'storage_type_id',
            [
                'foreignKey' => [
                    'message' => 'Hay almacenes registrados de este tipo de almacén.',
                ]
            ]
        );
    }
}
