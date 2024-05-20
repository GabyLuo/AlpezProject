<?php

use Phalcon\Mvc\Model;

class Users extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_users');

        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        $this->hasMany(
            'id',
            'Accounts',
            'admin_id',
            [
                'foreignKey' => [
                    'message' => 'Hay cuentas que dependen de este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Accounts',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay cuentas creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Accounts',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay cuentas actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Laminates',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay laminados creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Laminates',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay laminados actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionLotProcesses',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay procesos de lotes creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionLotProcesses',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay procesos de lotes actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionLots',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionLots',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay lotes actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Formulas',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay fórmulas creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Formulas',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay fórmulas actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionMeasurements',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay mediciones creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionMeasurements',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay mediciones actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionOrders',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay ordenes de producción creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionOrders',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay ordenes de producción actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionProcesses',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay procesos de producción creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionProcesses',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay procesos de producción actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionScraps',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay scraps creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductionScraps',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay scraps actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'PurchaseOrderDetails',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de orden de compra creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'PurchaseOrderDetails',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de orden de compra actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'PurchaseOrderDocuments',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay documentos de orden de compra creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'PurchaseOrderDocuments',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay documentos de orden de compra actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'PurchaseOrders',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay ordenes de compra creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'PurchaseOrders',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay ordenes de compra actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'RawMaterialShipments',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay recepciones creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'RawMaterialShipments',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay recepciones actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Samplings',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay muestras creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Samplings',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay muestras actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShipmentDetails',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay jumbos creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ShipmentDetails',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay jumbos actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Shipments',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay recepciones creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Shipments',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay recepciones actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Suppliers',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay proveedores creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Suppliers',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay proveedores actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BaleOpeningDetails',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de apertura de pacas creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BaleOpeningDetails',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de apertura de pacas actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BaleOpenings',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay aperturas de pacas creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BaleOpenings',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay aperturas de pacas actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'CustomerBranchOffices',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay sucursales de clientes creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'CustomerBranchOffices',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay sucursales de clientes actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Customers',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay clientes creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Customers',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay clientes actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceDetails',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra paca de remisiones creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceDetails',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra paca de remisiones actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceInBulkDetails',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra abierta de remisiones creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceInBulkDetails',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra abierta de remisiones actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceLaminateDetails',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra laminada de remisiones creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'InvoiceLaminateDetails',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de fibra laminada de remisiones actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Invoices',
            'agent_id',
            [
                'foreignKey' => [
                    'message' => 'Hay remisiones que dependen de este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Invoices',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay remisiones creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Invoices',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay remisiones actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Actions',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay acciones creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Actions',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay acciones actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Roles',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay roles creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Roles',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay roles actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'UserRoles',
            'user_id',
            [
                'foreignKey' => [
                    'message' => 'Hay roles asignados a este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'UserRoles',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay asignación de roles creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'UserRoles',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay asignación de roles actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Users',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay usuarios creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Users',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay usuarios actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Bales',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay pacas creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Bales',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay pacas actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BranchOffices',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay sucursales creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BranchOffices',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay sucursales actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BranchTransfers',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay traspasos de sucursal creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'BranchTransfers',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay traspasos de sucursal actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Categories',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay sucursales creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Categories',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay sucursales actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Drivers',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay choferes creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Drivers',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay choferes actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Lines',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay líneas creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Lines',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay líneas actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'MovementDetails',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de movimientos creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'MovementDetails',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay detalles de movimientos actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Movements',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay movimientos creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Movements',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay movimientos actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Operators',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay operadores creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Operators',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay operadores actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Products',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay productos creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Products',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay productos actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductsPrices',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay precios de productos creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'ProductsPrices',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay precios de productos actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'StorageTypes',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay tipos de almacén creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'StorageTypes',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay tipos de almacén actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Storages',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay almacenes creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Storages',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay almacenes actualizados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Transactions',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay transacciones creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Transactions',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay transacciones actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Units',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay unidades creadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'Units',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay unidades actualizadas por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'CustomerUsers',
            'user_id',
            [
                'foreignKey' => [
                    'message' => 'Este usuario pertenece a un cliente.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'CustomerUsers',
            'created_by',
            [
                'foreignKey' => [
                    'message' => 'Hay clientes de usuarios creados por este usuario.',
                ]
            ]
        );

        $this->hasMany(
            'id',
            'CustomerUsers',
            'updated_by',
            [
                'foreignKey' => [
                    'message' => 'Hay clientes de usuarios actualizados por este usuario.',
                ]
            ]
        );
    }
}