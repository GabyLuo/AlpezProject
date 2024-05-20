<?php

use Phalcon\Mvc\Model;

class Accounts extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_accounts');

        $this->belongsTo('admin_id', 'Users', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Users',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay usuarios que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Suppliers',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay proveedores que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Categories',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay categorías que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Storages',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay almacenes que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Laminates',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay laminados que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Customers',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay clientes que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BranchOffices',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay sucursales que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Drivers',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay choferes que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Operators',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay operadores que dependen de esta cuenta.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'StorageTypes',
            'account_id',
            [
                'foreignKey' => [
                    'message' => 'Hay tipos de almacén que dependen de esta cuenta.',
                ]
            ]
        );
    }
}
