<?php

use Phalcon\Mvc\Model;

class Storages extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_storages');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('branch_office_id', 'BranchOffices', 'id');
        $this->belongsTo('storage_type_id', 'StorageTypes', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',// id de wwms_storages
            'Movements',// la table que contiene la referncia osea el id de arriba
            'storage_id',// como se llama el camo que lo guarda es stiorage_id 
            [
                'foreignKey' => [
                    'message' => 'Hay movimientos que dependen de este almacén.',
                ]
            ]
        );
    }
}
