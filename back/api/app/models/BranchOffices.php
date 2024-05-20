<?php

use Phalcon\Mvc\Model;

class BranchOffices extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_branch_offices');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
        $this->belongsTo('postal_code_id', 'PostalCode', 'id',[
            'alias' => 'PostalCode',
        ]);
        $this->belongsTo('suburb_id', 'Suburb', 'id',[
            'alias' => 'Suburb',
        ]);

        $this->hasMany(
            'id',
            'Storages',
            'branch_office_id',
            [
                'foreignKey' => [
                    'message' => 'Hay almacenes que dependen de esta sucursal.',
                ]
            ]
        );
    }
}
