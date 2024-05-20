<?php

/**
 * Add your routes here
 */
use LazyControllerCollection as LazyCtrl;

$auth = new LazyCtrl(AuthController::class, '/auth');
$auth->post('/login', 'login');
$app->mount($auth);

$files = new LazyCtrl(FilesController::class, '/dirFiles');
$files->get('/{id:[0-9]+}', 'getAll');
$files->post('/', 'uploadFile');
$files->delete('/{id:[0-9]+}', 'delete');
$files->get('/getfile/{id:[0-9]+}', 'getFile');
$app->mount($files);

$users = new LazyCtrl(UsersController::class, '/users');
$users->get('/', 'getUsers');
$users->get('/{id:[0-9]+}', 'getUser');
$users->get('/profile', 'profile');
$users->get('/getSeller', 'getSeller');
$users->post('/', 'create');
$users->put('/{id:[0-9]+}', 'update');
$users->put('/roles/{id:[0-9]+}', 'updateRoles');
$users->put('/profile', 'updateProfile');
$users->get('/sellers', 'getSellersOptions');
$app->mount($users);

$roles = new LazyCtrl(RolesController::class, '/roles');
$roles->get('/options', 'getOptions');
$app->mount($roles);

$actions = new LazyCtrl(ActionsController::class, '/actions');
$actions->get('/', 'getActions');
$actions->put('/', 'update');
$app->mount($actions);

$accounts = new LazyCtrl(AccountsController::class, '/accounts');
$accounts->get('/', 'getAccounts');
$accounts->get('/{id:[0-9]+}', 'getAccount');
$accounts->get('/options', 'getOptions');
$app->mount($accounts);

$categories = new LazyCtrl(CategoriesController::class, '/categories');
$categories->get('/', 'getCategories');
$categories->get('/{id:[0-9]+}', 'getCategory');
$categories->get('/options', 'getOptions');
$categories->post('/', 'create');
$categories->put('/{id:[0-9]+}', 'update');
$categories->delete('/{id:[0-9]+}', 'delete');
$app->mount($categories);

$carriers = new LazyCtrl(CarriersController::class, '/carriers');
$carriers->get('/', 'getCarriers');
$carriers->get('/{id:[0-9]+}', 'getCarrier');
$carriers->get('/options', 'getOptions');
$carriers->post('/', 'create');
$carriers->put('/{id:[0-9]+}', 'update');
$carriers->delete('/{id:[0-9]+}', 'delete');
$app->mount($carriers);

$channels = new LazyCtrl(ChannelsController::class, '/channels');
$channels->get('/', 'getChannels');
$channels->get('/{id:[0-9]+}', 'getChannel');
$channels->get('/options', 'getOptions');
$channels->post('/', 'create');
$channels->put('/{id:[0-9]+}', 'update');
$channels->delete('/{id:[0-9]+}', 'delete');
$app->mount($channels);

$currencies = new LazyCtrl(CurrenciesController::class, '/currencies');
$currencies->get('/', 'getCurrencies');
$currencies->get('/{id:[0-9]+}', 'getCurrency');
$currencies->get('/options', 'getOptions');
$currencies->post('/', 'create');
$currencies->put('/{id:[0-9]+}', 'update');
$currencies->delete('/{id:[0-9]+}', 'delete');
$app->mount($currencies);

/* Enviar correos */
$mail = new ControllerCollection(new CorreosController(), '/mail');
$mail->get('/generatePDF', 'sendPdfToProvider');
$app->mount($mail);

$exchanges = new LazyCtrl(ExchangesController::class, '/exchanges');
$exchanges->get('/', 'getExchanges');
$exchanges->get('/{id:[0-9]+}', 'getExchange');
$exchanges->get('/options', 'getOptions');
$exchanges->get('/options/category/{id:[0-9]+}', 'getOptionsByExchangeId');
$exchanges->get('/getExchange/{id:[0-9]+}', 'getExchangeFromCurrency');
$exchanges->get('/getExchangePO/{id:[0-9]+}/{order_id:[0-9]+}', 'getExchangePO');
$exchanges->post('/', 'create');
$exchanges->put('/{id:[0-9]+}', 'update');
$exchanges->delete('/{id:[0-9]+}', 'delete');
$app->mount($exchanges);

$branchOffices = new LazyCtrl(BranchOfficesController::class, '/branch-offices');
$branchOffices->get('/', 'getBranchOffices');
$branchOffices->get('/clusters', 'getBranchOfficesWithCluster');
$branchOffices->get('/{id:[0-9]+}', 'getBranchOffice');
$branchOffices->get('/options', 'getOptions');
$branchOffices->get('/branchOptions', 'getBranchOptions');
$branchOffices->get('/customer/{id:[0-9]+}', 'getCustomerByBranchOffices');
$branchOffices->get('/getByCluster/{cluster_id}', 'getByCluster');
$branchOffices->get('/options/IndexShopping', 'getOptionsIndexShoppingCart');
$branchOffices->get('/getBranchsOffices', 'getBranchsOffices');
$branchOffices->post('/', 'create');
$branchOffices->put('/{id:[0-9]+}', 'update');
$branchOffices->delete('/{id:[0-9]+}', 'delete');
$branchOffices->get('/postal_codes/{code:[0-9]+}/{municipio}', 'postal_codes');
$branchOffices->get('/suburbs/{id:[0-9]+}', 'suburbs');
$branchOffices->get('/states', 'states');
$branchOffices->get('/municipalities/{state:[0-9]+}/{postal}', 'municipalities');
$branchOffices->get('/cities/{state:[0-9]+}/{postal}', 'cities');
$branchOffices->get('/getBranchOfficesToReportShopping', 'getBranchOfficesToReportShopping');
$app->mount($branchOffices);

$branchOfficesTaxCompanie = new LazyCtrl(BranchOfficeTaxCompaniesController::class, '/branch-offices-tax-companies');
$branchOfficesTaxCompanie->get('/branchOffice/{branchOfficeId:[0-9]+}', 'getBranchOfficesTaxCompanies');
$branchOfficesTaxCompanie->get('/{id:[0-9]+}', 'getBranchOfficeTaxCompany');
$branchOfficesTaxCompanie->post('/', 'create');
$branchOfficesTaxCompanie->put('/{id:[0-9]+}', 'update');
$branchOfficesTaxCompanie->delete('/{id:[0-9]+}', 'delete');
$app->mount($branchOfficesTaxCompanie);



$dashboard = new LazyCtrl(DashboardController::class, '/dashboard');
$dashboard->get('/getPurchases/{startDate}', 'getPurchases');
$dashboard->get('/getInventoryCostDaily', 'getInventoryCostDaily');
$dashboard->get('/getStockCostDaily', 'getStockCostDaily');
$dashboard->get('/getStockCostWeekly', 'getStockCostWeekly');
$dashboard->get('/getStockCostAnual', 'getStockCostAnual');
$dashboard->get('/getInventoryCostWeekly', 'getInventoryCostWeekly');
$dashboard->get('/getInventoryCostAnual', 'getInventoryCostAnual');
$dashboard->get('/getShoppingCartKpis', 'getShoppingCartKpis');
$dashboard->get('/getQtyByStatusShoppingCart', 'getQtyByStatusShoppingCart');
$dashboard->get('/salesByCustomer', 'salesByCustomer');
$dashboard->get('/salesBySeller', 'salesBySeller');
$dashboard->get('/salesBySellerWeekly', 'salesBySellerWeekly');
$dashboard->get('/salesBySellerDaily', 'salesBySellerDaily');
$dashboard->get('/monthAmounts', 'monthAmounts');
$dashboard->get('/getGpiOne', 'getGpiOne');
$dashboard->get('/dayProductionCharts', 'dayProductionCharts');
$dashboard->get('/weekProductionCharts', 'weekProductionCharts');
$dashboard->get('/stockProducts', 'stockProducts');
$dashboard->get('/monthProductionCharts', 'monthProductionCharts');
$dashboard->get('/accountsReceivable', 'accountsReceivable');
$dashboard->get('/getDataStation/{clusterId}/{stationId}', 'getDataStation');
$dashboard->get('/getDataMermas/{clusterId}/{stationId}', 'getDataMermas');
$app->mount($dashboard);

$storages = new LazyCtrl(StoragesController::class, '/storages');
$storages->get('/', 'getStorages');
$storages->get('/all', 'getStoragesWithAccountName');
$storages->get('/{id:[0-9]+}', 'getStorage');
$storages->get('/branch-office/{id:[0-9]+}', 'getStoragesByBranchOfficeId');
$storages->get('/options', 'getOptions');
$storages->get('/{id:[0-9]+}/bags', 'getBagsByStorageId');
$storages->get('/{id:[0-9]+}/bales', 'getBalesByStorageId');
$storages->get('/{id:[0-9]+}/fibers', 'getFiberProductsByStorageId');
$storages->get('/{id:[0-9]+}/bulk-products', 'getBulkProductsByStorageId');
$storages->post('/{id:[0-9]+}/bulk-products', 'getBulkProductsByStorageIdByPagination');
$storages->get('/{id:[0-9]+}/laminates', 'getLaminateProductsByStorageId');
$storages->get('/{id:[0-9]+}/raw-materials', 'getRawMaterialProductsByStorageId');
$storages->post('/{id:[0-9]+}/raw-materials-data', 'getRawMaterialProductsByStorageIdData');
$storages->post('/{id:[0-9]+}/raw-materials-data-to-out', 'getRawMaterialProductsByStorageIdDataToOutProduct');
$storages->get('/getStoragesbyShoppingCart/{id:[0-9]+}', 'getStoragesbyShoppingCart');
$storages->get('/validation/{id:[0-9]+}', 'getValidation');
$storages->get('/getStoragesOfBranch/{id:[0-9]+}', 'getStoragesOfBranch');
// $storages->get('/options/stock', 'getStoragesOptionsWithStock');
$storages->post('/', 'create');
$storages->put('/{id:[0-9]+}', 'update');
$storages->delete('/{id:[0-9]+}', 'delete');
$storages->get('/getUnitProduct/{id:[0-9]+}', 'getUnitProduct');
$app->mount($storages);

$storageTypes = new LazyCtrl(StorageTypesController::class, '/storage-types');
$storageTypes->get('/', 'getStorageTypes');
$storageTypes->get('/{id:[0-9]+}', 'getStorageType');
$storageTypes->get('/options', 'getOptions');
$storageTypes->post('/', 'create');
$storageTypes->put('/{id:[0-9]+}', 'update');
$storageTypes->delete('/{id:[0-9]+}', 'delete');
$app->mount($storageTypes);

$lines = new LazyCtrl(LinesController::class, '/lines');
$lines->get('/', 'getLines');
$lines->get('/{id:[0-9]+}', 'getLine');
$lines->get('/options', 'getOptions');
$lines->get('/options/category/{id:[0-9]+}', 'getOptionsByCategoryId');
$lines->post('/', 'create');
$lines->put('/{id:[0-9]+}', 'update');
$lines->delete('/{id:[0-9]+}', 'delete');
$app->mount($lines);

$units = new LazyCtrl(UnitsController::class, '/units');
$units->get('/', 'getUnits');
$units->get('/{id:[0-9]+}', 'getUnit');
$units->get('/options', 'getOptions');
$units->post('/', 'create');
$units->put('/{id:[0-9]+}', 'update');
$units->delete('/{id:[0-9]+}', 'delete');
$app->mount($units);

$products = new LazyCtrl(ProductsController::class, '/products');
$products->get('/', 'getProducts');
$products->get('/active', 'getActiveProducts');
$products->get('/{id:[0-9]+}', 'getProduct');
$products->get('/productsWithPrice/{priceLevel}/{type_order}/{id:[0-9]+}', 'productsWithPrice');
$products->post('/productsWithPriceByFilter', 'productsWithPriceByFilter');
$products->get('/line/{id:[0-9]+}', 'getProductsByLineId');
$products->get('/category/{id:[0-9]+}', 'getProductsByCategoryId');
$products->get('/category1/{id:[0-9]+}', 'getProductsByCategoryId1');
$products->get('/category2', 'getAllProducts');
$products->get('/stock', 'getProductsWithStock');
$products->get('/options', 'getOptions');
$products->get('/options/kardex', 'getOptionsKardex');
$products->get('/csv/{categoryId}/{lineId}', 'getCsvProducts');
$products->get('/csvprices/{categoryId}/{lineId}/{markId}', 'getCsvProductsPrices');
$products->get('/options/purchase-order/{id:[0-9]+}', 'getOptionsByPurchaseOrder');
$products->get('/options/shipment/{id:[0-9]+}', 'getOptionsByShipment');
$products->get('/options/line/{id:[0-9]+}', 'getOptionsByLineId');
$products->get('/options/category/{id:[0-9]+}', 'getOptionsByCategoryId');
$products->get('/options/main', 'getMainOptions');
$products->get('/options/family/{id:[0-9]+}', 'getOptionsByFamilyId');
$products->get('/searchClave/{filter}', 'searchClave');
$products->get('/searchProducts/{filter}', 'searchProducts');
$products->get('/getProductsBy', 'getProductsBy');
$products->get('/getPhotos/{id:[0-9]+}', 'getPhotos');
$products->get('/getImagesbyCart/{id:[0-9]+}', 'getImagesbyCart');
$products->post('/', 'create');
$products->post('/pag', 'getProductsByPagination');
$products->post('/pagPrices', 'getProductsPricesByPagination');
$products->post('/photo', 'changePhoto');
$products->post('/photoDelete', 'deletePhoto');
$products->post('/report', 'getReportPurchases');
$products->put('/{id:[0-9]+}', 'update');
$products->put('/{id:[0-9]+}/prices', 'addPrice');
$products->delete('/{id:[0-9]+}', 'delete');
$products->post('/file', 'uploadFileProducts');
$products->post('/file2', 'uploadFile');
$app->mount($products);

$productsPrices = new LazyCtrl(ProductsPricesController::class, '/products-prices');
$productsPrices->get('/', 'getProductsPrices');
$productsPrices->get('/{id:[0-9]+}', 'getProductPrices');
$productsPrices->get('/level/{level}', 'getLevelProductsPrices');
$productsPrices->post('/', 'create');
// $productsPrices->post('/filePrices', 'updateAllPricesIntento');
$productsPrices->post('/filePrices', 'updateAllPricesIntento');
$productsPrices->put('/{id:[0-9]+}', 'update');
$productsPrices->delete('/{id:[0-9]+}', 'delete');
$app->mount($productsPrices);

$suppliers = new LazyCtrl(SuppliersController::class, '/suppliers');
$suppliers->get('/getAll','getAll');
$suppliers->get('/', 'getSuppliers');
$suppliers->get('/{id:[0-9]+}', 'getSupplier');
$suppliers->get('/options', 'getOptions');
$suppliers->post('/', 'create');
$suppliers->post('/pag', 'getSuppliersByPagination');
$suppliers->put('/{id:[0-9]+}', 'update');
$suppliers->delete('/{id:[0-9]+}', 'delete');
$suppliers->get('/csv/{branchOfficeId}', 'getCsvSuppliers');
$suppliers->get('/pdf', 'getPdf');
$suppliers->post('/photo', 'changePhoto');
$suppliers->get('/getSuppliersOrders', 'getSuppliersOrders');
$suppliers->get('/getSuppliersToReportSales', 'getSuppliersToReportSales');
$app->mount($suppliers);

$shipments = new LazyCtrl(ShipmentsController::class, '/shipments');
$shipments->get('/', 'getShipments');
$shipments->get('/all', 'getShipmentsWithOrderSerial');
$shipments->get('/arribeOcs', 'getShipmentsStatus');
$shipments->get('/analyzed', 'getAnalyzedShipments');
$shipments->get('/order/{id:[0-9]+}', 'getShipmentsByOrderId');
$shipments->get('/{id:[0-9]+}', 'getShipment');
$shipments->get('/options', 'getOptions');
$shipments->get('/pdf/{id:[0-9]+}', 'getPdf');
$shipments->get('/pdf-qr/{shipmentId}', 'getPdfQr');
$shipments->get('/{id:[0-9]+}/send-mail', 'sendPdfToProvider');
$shipments->get('/invoice-file/download/{id:[0-9]+}', 'downloadInvoiceFile');
$shipments->post('/', 'create');
$shipments->post('/invoice-file/{id:[0-9]+}', 'uploadInvoiceFile');
$shipments->post('/report', 'getShipmentsReport');
$shipments->put('/{id:[0-9]+}', 'update');
$shipments->put('/analyzed/{id:[0-9]+}', 'analyzed');
$shipments->put('/reject/{id:[0-9]+}', 'reject');
$shipments->put('/{id:[0-9]+}/entry', 'entry');
$shipments->delete('/{id:[0-9]+}', 'delete');
$app->mount($shipments);

$shipmentDetails = new LazyCtrl(ShipmentDetailsController::class, '/shipment-details');
$shipmentDetails->get('/shipment/{id:[0-9]+}', 'getShipmentDetailsByShipmentId');
$shipmentDetails->get('/pdf-qr/{shipmentDetailId}', 'getPdfQr');
$shipmentDetails->get('/bags', 'getAvailableBags');
$shipmentDetails->get('/bags/all', 'getAllBags');
$shipmentDetails->get('/qtyProducts/{id:[0-9]+}/{product:[0-9]+}', 'getQtyProducts');
$shipmentDetails->post('/', 'create');
$shipmentDetails->post('/bag/qr/{id:[0-9]+}', 'getShipmentDetailByQr');
$shipmentDetails->put('/{id:[0-9]+}', 'update');
$shipmentDetails->delete('/{id:[0-9]+}', 'delete');
$shipmentDetails->post('/addAll', 'addAll');
$app->mount($shipmentDetails);


$forecastDebts = new LazyCtrl(ForecastDebtsToPayController::class, '/forecast-debts');
$forecastDebts->post('/getDebtsToPay', 'getDebtsToPay');
$forecastDebts->post('/getDebtsToPayForSuppliers', 'getDebtsToPayForSuppliers');
$forecastDebts->get('/getDataCalendar/{id:[0-9]+}', 'getDataCalendar');
$app->mount($forecastDebts);


$samplings = new LazyCtrl(SamplingsController::class, '/samplings');
$samplings->get('/', 'getSamplings');
$samplings->get('/shipment/{id:[0-9]+}', 'getSamplingsByShipmentId');
$samplings->get('/{id:[0-9]+}', 'getSampling');
$samplings->get('/options', 'getOptions');
$samplings->get('/pdf/{id:[0-9]+}', 'getPdf');
$samplings->post('/', 'create');
$samplings->put('/{id:[0-9]+}', 'update');
$samplings->delete('/{id:[0-9]+}', 'delete');
$app->mount($samplings);

$purchaseOrders = new LazyCtrl(PurchaseOrdersController::class, '/purchase-orders');
$purchaseOrders->get('/', 'getOrders');
$purchaseOrders->get('/searchClave/{filter}', 'searchClave');
$purchaseOrders->get('/getOptions2', 'getOptionsWithSupplier');
$purchaseOrders->get('/all', 'getOrdersWithSupplierName');
$purchaseOrders->get('/{id:[0-9]+}', 'getOrder');
$purchaseOrders->get('/options', 'getOptions');
$purchaseOrders->get('/pdf/{id:[0-9]+}', 'getPdf');
$purchaseOrders->get('/{id:[0-9]+}/send-mail', 'sendPdfToProvider');
$purchaseOrders->post('/', 'create');
$purchaseOrders->post('/getPurchaseGridPayments', 'getPurchasesPaymentsGridPagination');
$purchaseOrders->post('/dataFromPurchaseOrder', 'dataFromPurchaseOrder');
$purchaseOrders->post('/addPayment', 'addPayment');
$purchaseOrders->post('/uploadPaymentFile/{id:[0-9]+}', 'uploadPaymentFile');
$purchaseOrders->post('/deletePayment', 'deletePayment');
$purchaseOrders->post('/getGridPayments', 'getGridPayments');
$purchaseOrders->get('/getPdfFromPurchasePayments/{supplier}/{status}/{saledatev1}/{saledatev2}', 'getPdfFromPurchasePayments');
$purchaseOrders->post('/sendEmail/{id:[0-9]+}', 'sendEmail');
$purchaseOrders->put('/{id:[0-9]+}', 'update');
$purchaseOrders->put('/request/{id:[0-9]+}', 'request');
$purchaseOrders->put('/approve/{id:[0-9]+}', 'approve');
$purchaseOrders->put('/close/{id:[0-9]+}', 'close');
$purchaseOrders->put('/cancel/{id:[0-9]+}', 'cancel');
$purchaseOrders->put('/open/{id:[0-9]+}', 'open');
$purchaseOrders->put('/changeStatus', 'changeStatus');
$purchaseOrders->put('/autorizar', 'Autorizar');
$purchaseOrders->delete('/{id:[0-9]+}', 'delete');
$purchaseOrders->put('/deleteFilePayment/{id:[0-9]+}', 'deleteFilePayment');
$purchaseOrders->post('/getGrid', 'getGrid');
$purchaseOrders->put('/updateDateInvoices/{id:[0-9]+}', 'updateDateInvoices');
$app->mount($purchaseOrders);

$purchaseOrderDetails = new LazyCtrl(PurchaseOrderDetailsController::class, '/purchase-order-details');
$purchaseOrderDetails->get('/', 'getOrderDetails');
$purchaseOrderDetails->get('/order/{id:[0-9]+}', 'getOrderDetailsByOrderId');
$purchaseOrderDetails->get('/{id:[0-9]+}', 'getOrderDetail');
$purchaseOrderDetails->post('/', 'create');
$purchaseOrderDetails->put('/{id:[0-9]+}', 'update');
$purchaseOrderDetails->delete('/{id:[0-9]+}', 'delete');
$purchaseOrderDetails->get('/getLastPrice/{id:[0-9]+}/{idp:[0-9]+}', 'getLastPrice');
$purchaseOrderDetails->get('/getLastPrice2/{id:[0-9]+}/{idp:[0-9]+}', 'getLastPrice2');
$purchaseOrderDetails->get('/getPdfquotationNote/{id:[0-9]+}', 'getPdfquotationNote');
$purchaseOrderDetails->post('/getReportShopping', 'getReportShopping');
$purchaseOrderDetails->get('/getReportShoppingToPDF/{dateini}/{datefin}/{supplier}/{product}', 'getReportShoppingToPDF');
$purchaseOrderDetails->get('/getReportShoppingToCSV/{dateini}/{datefin}/{supplier}/{product}', 'getReportShoppingToCSV');
$purchaseOrderDetails->post('/shoppingOfSuppliers', 'shoppingOfSuppliers');
$purchaseOrderDetails->get('/shoppingOfSuppliersPDF/{supplier}/{dataini}/{datafin}/{officeBranch}', 'shoppingOfSuppliersPDF');
$purchaseOrderDetails->get('/getReportShoppingToCSVShoppingSupplier/{supplier}/{dataini}/{datafin}/{officeBranch}', 'getReportShoppingToCSVShoppingSupplier');
$purchaseOrderDetails->get('/getProducts', 'getProducts');
$app->mount($purchaseOrderDetails);

$purchaseOrderDocuments = new LazyCtrl(PurchaseOrderDocumentsController::class, '/purchase-order-documents');
$purchaseOrderDocuments->get('/purchase-order/{id:[0-9]+}', 'getPurchaseOrderDocumentsByPurchaseOrderId');
$purchaseOrderDocuments->get('/file/{id:[0-9]+}/download', 'downloadDocumentFile');
$purchaseOrderDocuments->post('/', 'create');
$purchaseOrderDocuments->post('/file/{id:[0-9]+}', 'uploadFile');
$purchaseOrderDocuments->put('/{id:[0-9]+}', 'update');
$purchaseOrderDocuments->delete('/{id:[0-9]+}', 'delete');
$app->mount($purchaseOrderDocuments);

$rawMaterialShipments = new LazyCtrl(RawMaterialShipmentsController::class, '/raw-material-shipments');
$rawMaterialShipments->get('/', 'getRawMaterialShipments');
$rawMaterialShipments->get('/{id:[0-9]+}', 'getRawMaterialShipment');
$rawMaterialShipments->post('/', 'create');
$rawMaterialShipments->put('/{id:[0-9]+}/execute', 'execute');
$app->mount($rawMaterialShipments);

$rawMaterialShipmentsDetails = new LazyCtrl(RawMaterialShipmentDetailsController::class, '/raw-material-shipments-details');
$rawMaterialShipmentsDetails->get('/raw-material-shipment/{id:[0-9]+}', 'getRawMaterialShipmentDetailsByRawMaterialShipmentId');
$rawMaterialShipmentsDetails->post('/', 'create');
$rawMaterialShipmentsDetails->put('/{id:[0-9]+}', 'update');
$rawMaterialShipmentsDetails->delete('/{id:[0-9]+}', 'delete');
$app->mount($rawMaterialShipmentsDetails);

$movements = new LazyCtrl(MovementsController::class, '/movements');
$movements->post('/', 'create');
$movements->put('/{id:[0-9]+}', 'update');
$movements->delete('/{id:[0-9]+}', 'delete');
$movements->get('/','getMovements');
$movements->get('/getFolio/{id:[0-9]+}', 'getFolioConsecutivo');
$movements->get('/out/{id:[0-9]+}', 'getMovementOut');
$movements->get('/entry/{id:[0-9]+}', 'getMovementEntry');
$movements->put('/execute/{id:[0-9]+}', 'execute');
$movements->get('/kardex/{startDate}/{endingDate}/{branchOfficeId}/{storageId}/{productId}', 'getKardex');
$movements->get('/generateMultiKardex/{startDate}/{endingDate}/{branchOfficeId}/{storageId}/{productId}/{$details}', 'generateMultiKardex');
$movements->get('/kardex/csv/{startDate}/{endingDate}/{branchOfficeId}/{storageId}/{productId}', 'getCsvKardex');
$movements->get('/kardex/pdf/{startDate}/{endingDate}/{branchOfficeId}/{storageId}/{productId}', 'getPdfKardex');
$movements->get('/storageInventory/{branchOfficeId}/{storageId}/{categoryId}/{lineId}/{productId}/{date}', 'getStorageInventory');
$movements->get('/storageInventoryv2/{branchOfficeId}/{storageId}/{categoryId}/{lineId}/{productId}/{date}', 'getStorageInventoryv2');
$movements->post('/storageInventoryMinimal', 'getStorageInventoryMinimal');
$movements->post('/storageInventoryByMark', 'getStorageInventoryByMark');
$movements->get('/movementPdf/{movementId}','movementPdf');
$movements->get('/movementPdfsi/{movementId}','movementPdfsi');
$movements->post('/getDataProducts', 'getDataProducts');
$movements->get('/inventory/pdf/{user}/{branchOfficeId}/{storageId}/{categoryId}/{lineId}/{productId}/{date}', 'getInventoryPdf');
$movements->get('/inventory-minimal-stock/pdf/{branchOfficeId}/{storageId}/{categoryId}/{lineId}/{productId}/{user}/{mark}', 'getInventoryMinimalStockPdf');
$movements->get('/inventory-minimal-stock/csv/{branchOfficeId}/{storageId}/{categoryId}/{lineId}/{productId}/{user}/{mark}', 'getInventoryMinimalStockCsv');
$movements->get('/inventorybymark/pdf/{branchOfficeId}/{storageId}/{categoryId}/{lineId}/{productId}/{user}/{mark}', 'getInventorybMSPdf');
$movements->get('/inventorybymark/csv/{branchOfficeId}/{storageId}/{categoryId}/{lineId}/{productId}/{user}/{mark}', 'getInventorybMSCsv');
$movements->put('/cancel/{id:[0-9]+}', 'cancel');
$app->mount($movements);

$movementDetail = new LazyCtrl(MovementDetailsController::class, '/movement-details');
$movementDetail->get('/{id:[0-9]+}', 'getMovementDetail');
$movementDetail->post('/', 'create');
$movementDetail->put('/{id:[0-9]+}', 'update');
$movementDetail->delete('/{id:[0-9]+}', 'delete');
$movementDetail->get('/csv', 'getCsvProducts');
$movementDetail->post('/file/{id:[0-9]+}', 'uploadFile');
$app->mount($movementDetail);

$productionOrders = new LazyCtrl(ProductionOrdersController::class, '/production-orders');
$productionOrders->get('/', 'getOrders');
$productionOrders->get('/{id:[0-9]+}', 'getOrder');
$productionOrders->post('/getByOrder/{id:[0-9]+}', 'getByOrder');
$productionOrders->post('/finalizeOrder/{id:[0-9]+}', 'finalizeOrder');
$productionOrders->get('/handi-work/pdf/{id:[0-9]+}', 'getHandiWorkPdf');
$productionOrders->get('/packing-list/pdf/{id:[0-9]+}', 'getPackingListPdf');
$productionOrders->get('/cost/pdf/{id:[0-9]+}', 'getCostPdf');

$productionOrders->post('/', 'create');
$productionOrders->post('/createorders', 'createorders');
$productionOrders->put('/{id:[0-9]+}', 'update');
$productionOrders->put('/{id:[0-9]+}/cancel', 'cancel');
$productionOrders->delete('/{id:[0-9]+}', 'delete');
$app->mount($productionOrders);

$productionLots = new LazyCtrl(ProductionLotsController::class, '/production-lots');
$productionLots->get('/', 'getLots');
$productionLots->get('/{id:[0-9]+}', 'getLot');
$productionLots->get('/order/{id:[0-9]+}', 'getLotsByOrderId');
$productionLots->get('/scheduled-start-date/{date}', 'getLotsByScheduledStartDate');
$productionLots->get('/{lotId:[0-9]+}/finished-products', 'getFinishedProductsByLotId');
$productionLots->get('/{lotId:[0-9]+}/raw-materials', 'getRawMaterialsByLotId');
$productionLots->get('/{lotId:[0-9]+}/returned-raw-materials', 'getReturnedRawMaterialsByLotId');
$productionLots->get('/finished-product/pdf/{id:[0-9]+}', 'getFinishedProductPdf');
$productionLots->get('/raw-material/pdf/{id:[0-9]+}', 'getRawMaterialsPdf');
$productionLots->get('/packing-list/pdf/{id:[0-9]+}', 'getPackingListPdf');
$productionLots->get('/scrap/pdf/{id:[0-9]+}', 'getScrapPdf');
$productionLots->get('/quality/pdf/{id:[0-9]+}', 'getQualityPdf');
$productionLots->get('/scheduled-calendar/{year:20\d{2}}/{month:(0[1-9]|1[0-2])}', 'getScheduledCalendarPdf');
$productionLots->post('/', 'create');
$productionLots->post('/raw-material/{id:[0-9]+}', 'addRawMaterialBag');
$productionLots->post('/return-raw-material/{id:[0-9]+}', 'addRawMaterialBagReturn');
$productionLots->post('/executelot/{id:[0-9]+}', 'executelot');
$productionLots->post('/finalizeLot/{id:[0-9]+}', 'finalizeLot');
$productionLots->post('/finalizeLotReturMaterial/{id:[0-9]+}', 'finalizeLotReturMaterial');
$productionLots->post('/getfinalizelot/{id:[0-9]+}', 'getfinalizelot');
$productionLots->put('/{id:[0-9]+}', 'update');
$productionLots->put('/{id:[0-9]+}/quality', 'updateQuality');
$productionLots->put('/raw-material/{id:[0-9]+}', 'updateRawMaterial');
$productionLots->put('/movement/{id:[0-9]+}', 'addMovement');
$productionLots->put('/execute-return-raw-materials-movement/{id:[0-9]+}', 'executeReturnRawMaterialsMovements');
$productionLots->put('/{id:[0-9]+}/cancel', 'cancel');
$productionLots->delete('/{id:[0-9]+}', 'delete');
$productionLots->post('/createLotByOrder', 'createLotByOrder');
$app->mount($productionLots);


$productionLotProcesses = new LazyCtrl(ProductionLotProcessesController::class, '/production-lot-processes');
$productionLotProcesses->get('/{id:[0-9]+}', 'getProcess');
$productionLotProcesses->get('/lot/{id:[0-9]+}', 'getProcessesByLotId');
$productionLotProcesses->put('/{id:[0-9]+}/start', 'start');
$productionLotProcesses->put('/{id:[0-9]+}/finish', 'finish');
// $productionLotProcesses->post('/', 'create');
// $productionLotProcesses->put('/{id:[0-9]+}', 'update');
// $productionLotProcesses->delete('/{id:[0-9]+}', 'delete');
$app->mount($productionLotProcesses);

$productionProcess = new LazyCtrl(ProductionProcessesController::class, '/production-processes');
$productionProcess->get('/', 'getProductionProcesses');
$productionProcess->get('/{id:[0-9]+}', 'getProductionProcess');
$productionProcess->get('/options', 'getOptions');
$productionProcess->post('/', 'create');
$productionProcess->put('/{id:[0-9]+}', 'update');
$productionProcess->delete('/{id:[0-9]+}', 'delete');
$app->mount($productionProcess);

$productionMeasurement = new LazyCtrl(ProductionMeasurementsController::class, '/production-measurements');
$productionMeasurement->get('/', 'getMeasurements');
$productionMeasurement->get('/{id:[0-9]+}', 'getMeasurement');
$productionMeasurement->get('/lot/{id:[0-9]+}', 'getMeasurementsByLotId');
$productionMeasurement->get('/lot/{lotId:[0-9]+}/process/{processId:[0-9]+}/dryer-number/{dryerNumber}', 'getMeasurementsByLotAndProcess');
$productionMeasurement->post('/', 'create');
$productionMeasurement->put('/{id:[0-9]+}', 'update');
$productionMeasurement->delete('/{id:[0-9]+}', 'delete');
$app->mount($productionMeasurement);

$productionScrap = new LazyCtrl(ProductionScrapsController::class, '/production-scraps');
$productionScrap->get('/', 'getScraps');
$productionScrap->get('/{id:[0-9]+}', 'getScrap');
$productionScrap->get('/lot/{id:[0-9]+}', 'getScrapsByLotId');
$productionScrap->get('/lot/{lotId:[0-9]+}/process/{processId:[0-9]+}/dryer-number/{dryerNumber}', 'getScrapByLotAndProcess');
$productionScrap->post('/', 'create');
$productionScrap->put('/{id:[0-9]+}', 'update');
$productionScrap->delete('/{id:[0-9]+}', 'delete');
$app->mount($productionScrap);

$formulas = new LazyCtrl(FormulasController::class, '/formulas');
$formulas->get('/', 'getFormulas');
$formulas->get('/{id:[0-9]+}', 'getFormula');
$formulas->get('/lot/{id:[0-9]+}', 'getFormulasByLotId');
$formulas->post('/', 'create');
$formulas->put('/{id:[0-9]+}', 'update');
$formulas->delete('/{id:[0-9]+}', 'delete');
$app->mount($formulas);

$transactions = new LazyCtrl(TransactionsController::class, '/transactions');
$transactions->get('/', 'getTransactions');
$transactions->get('/{id:[0-9]+}', 'getTransaction');
$transactions->put('/execute/{id:[0-9]+}', 'execute');
$app->mount($transactions);

$branchTransfers = new LazyCtrl(BranchTransfersController::class, '/branch-transfers');
$branchTransfers->get('/', 'getBranchTransfers');
$branchTransfers->put('/{id:[0-9]+}', 'update');
$branchTransfers->get('/{id:[0-9]+}', 'getBranchTransfer');
$branchTransfers->get('/pdf/{id:[0-9]+}', 'getPdf');
$branchTransfers->post('/', 'create');
$app->mount($branchTransfers);

$branchTransferDetails = new LazyCtrl(BranchTransferDetailsController::class, '/branch-transfer-details');
$branchTransferDetails->get('/branch-transfer/{branchTransferId:[0-9]+}/raw-materials', 'getRawMaterialsByTransactionId');
$branchTransferDetails->get('/branch-transfer/{branchTransferId:[0-9]+}/inBulk-transaction', 'inBulkTransaction');
$branchTransferDetails->post('/', 'create');
$branchTransferDetails->delete('/branch-transfer/{branchTransferId:[0-9]+}/bale/{baleId:[0-9]+}', 'deleteBranchTransferBale');
$branchTransferDetails->delete('/branch-transfer/{branchTransferId:[0-9]+}/laminate/{laminateId:[0-9]+}', 'deleteBranchTransferLaminate');
$branchTransferDetails->delete('/branch-transfer/{branchTransferId:[0-9]+}/inbulk/{inbulkId:[0-9]+}', 'deleteBranchTransferinBulk');
$branchTransferDetails->delete('/branch-transfer/{branchTransferId:[0-9]+}/raw-material/{rawMaterialId:[0-9]+}', 'deleteBranchTransferRawMaterial');
$app->mount($branchTransferDetails);

$baleOpenings = new LazyCtrl(BaleOpeningsController::class, '/bale-openings');
$baleOpenings->get('/', 'getBaleOpenings');
$baleOpenings->get('/{id:[0-9]+}', 'getBaleOpening');
$baleOpenings->post('/', 'create');
$baleOpenings->put('/execute/{id:[0-9]+}', 'execute');
$baleOpenings->delete('/{id:[0-9]+}', 'delete');
$app->mount($baleOpenings);

$baleOpeningDetails = new LazyCtrl(BaleOpeningDetailsController::class, '/bale-opening-details');
$baleOpeningDetails->get('/', 'getBaleOpeningsDetails');
$baleOpeningDetails->get('/{id:[0-9]+}', 'getBaleOpeningDetails');
$baleOpeningDetails->post('/', 'create');
$baleOpeningDetails->delete('/{id:[0-9]+}', 'delete');
$app->mount($baleOpeningDetails);

$bales = new LazyCtrl(BalesController::class, '/bales');
$bales->get('/', 'getBales');
$bales->get('/options', 'getBaleOptions');
$bales->get('/transaction/{id:[0-9]+}', 'getBalesByTransactionId');
$bales->post('/', 'create');
$bales->put('/{id:[0-9]+}', 'update');
$bales->delete('/{id:[0-9]+}', 'delete');
$app->mount($bales);

$customers = new LazyCtrl(CustomersController::class, '/customers');
$customers->get('/', 'getCustomers');
$customers->get('/getAll', 'getAll');
$customers->get('/getLastCode', 'getLastCode');
$customers->get('/{id:[0-9]+}', 'getCustomer');
$customers->get('/options', 'getOptions');
$customers->get('/getCustomersBySeller/{sellerId:[0-9]+}', 'getCustomersBySeller');
$customers->post('/officeoptions', 'getOptionsOffices');
$customers->post('/pag', 'getCustomersByPagination');
$customers->post('/getDataClient', 'getDataClient');
$customers->post('/getDataClientBranch', 'getDataClientBranch');
$customers->post('/', 'create');
$customers->put('/{id:[0-9]+}', 'update');
$customers->put('/requirement/{id:[0-9]+}', 'updateRequirement');
$customers->delete('/{id:[0-9]+}', 'delete');
$customers->get('/csv/{branchOfficeId}', 'getCsvCustomers');
$customers->post('/photo', 'changePhoto');
$customers->post('/file', 'uploadFileCustomers');
$app->mount($customers);

$customerBranchOffices = new LazyCtrl(CustomerBranchOfficesController::class, '/customer-branch-offices');
$customerBranchOffices->get('/customer/{customerId:[0-9]+}', 'getCustomerBranchOffices');
$customerBranchOffices->get('/{id:[0-9]+}', 'getCustomerBranchOffice');
$customerBranchOffices->get('/options', 'getOptions');
$customerBranchOffices->post('/', 'create');
$customerBranchOffices->put('/{id:[0-9]+}', 'update');
$customerBranchOffices->delete('/{id:[0-9]+}', 'delete');
$app->mount($customerBranchOffices);

$customerContacts = new LazyCtrl(CustomerContactsController::class, '/customer-contacts');
$customerContacts->get('/customer/{customerId:[0-9]+}', 'getCustomerContacts');
$customerContacts->get('/{id:[0-9]+}', 'getCustomerContact');
$customerContacts->get('/options', 'getOptions');
$customerContacts->get('/getContacts/{id:[0-9]+}', 'getContacts');
$customerContacts->post('/', 'create');
$customerContacts->put('/{id:[0-9]+}', 'update');
$customerContacts->delete('/{id:[0-9]+}', 'delete');
$app->mount($customerContacts);

$supplierContacts = new LazyCtrl(SupplierContactsController::class, '/supplier-contacts');
$supplierContacts->get('/supplier/{supplierId:[0-9]+}', 'getSupplierContacts');
$supplierContacts->get('/{id:[0-9]+}', 'getSupplierContact');
$supplierContacts->get('/options', 'getOptions');
$supplierContacts->post('/', 'create');
$supplierContacts->put('/{id:[0-9]+}', 'update');
$supplierContacts->delete('/{id:[0-9]+}', 'delete');
$app->mount($supplierContacts);

$supplierDossiers = new LazyCtrl(SupplierDossiersController::class, '/supplier-dossiers');
$supplierDossiers->get('/all/{id:[0-9]+}', 'getDossiers');
$supplierDossiers->get('/{id:[0-9]+}', 'getDossier');
$supplierDossiers->post('/', 'create');
$supplierDossiers->put('/{id:[0-9]+}', 'update');
$supplierDossiers->delete('/{id:[0-9]+}', 'delete');
$supplierDossiers->post('/file/expense/{id:[0-9]+}', 'createFileExpense'); 
$app->mount($supplierDossiers);

$customerTaxCompanies = new LazyCtrl(CustomerTaxCompaniesController::class, '/customer-tax-companies');
$customerTaxCompanies->get('/customer/{customerId:[0-9]+}', 'getCustomerTaxCompanies');
$customerTaxCompanies->get('/{id:[0-9]+}', 'getCustomerTaxCompany');
$customerTaxCompanies->get('/getcustomerTaxCompanyByClient/{id:[0-9]+}', 'getcustomerTaxCompanyByClient');
$customerTaxCompanies->get('/options', 'getOptions');
$customerTaxCompanies->post('/', 'create');
$customerTaxCompanies->put('/{id:[0-9]+}', 'update');
$customerTaxCompanies->delete('/{id:[0-9]+}', 'delete');
$app->mount($customerTaxCompanies);


$oldbalance = new LazyCtrl(OldBalanceController::class, '/oldbalance');
$oldbalance->post('/getOldbalance', 'getOldbalance');
$oldbalance->get('/getClients', 'getClients');
$oldbalance->get('/getPdf/{customer}/{status}/{branch}/{filter}', 'getPdf');
$oldbalance->get('/getCSV/{customer}/{status}/{branch}/{filter}', 'getCSV');
$app->mount($oldbalance);


$closesales = new LazyCtrl(CloseSalesController::class, '/close-sale');
$closesales->get('/closeSales/{getDate}/{remission}', 'closeSales');
$closesales->get('/getCloseSales/{getDate}', 'getCloseSales');
$closesales->get('/getCsvCloseSales/{getDate}/{remission}', 'getCsvCloseSales');
$closesales->get('/getCloseSalesRemission/{getDate}', 'getCloseSalesRemission');
$closesales->get('/getCloseSalesLoan/{getDate}', 'getCloseSalesLoan');
$closesales->get('/closeSalesLoanPDF/{getDate}', 'closeSalesLoanPDF');
$closesales->get('/getCsvCloseSalesLoanPdf/{getDate}', 'getCsvCloseSalesLoanPdf');
$app->mount($closesales);

$invoices = new LazyCtrl(InvoicesController::class, '/invoices');
$invoices->get('/quotationNotePDF/{id:[0-9]+}/{order}', 'quotationNotePDF');
$invoices->get('/idRemmision', 'idRemmision');
$invoices->get('/', 'getInvoices');
$invoices->get('/file/{id:[0-9]+}/download', 'downloadDocumentFilePallet');
$invoices->post('/file/{id:[0-9]+}', 'uploadFile');
$invoices->get('/payments', 'getPayments');
$invoices->get('/{id:[0-9]+}', 'getInvoice');
$invoices->get('/pdf/{id:[0-9]+}', 'getPdf');
$invoices->get('/{id:[0-9]+}/send-mail', 'sendPdfToCustomer');
$invoices->get('/deliver/{id}/{token}', 'deliver');
$invoices->get('/{id:[0-9]+}/document-file/download', 'downloadDocumentFile');
$invoices->get('/formaPagoOptions', 'getFormaPagoOptions');
$invoices->get('/usoCFDIOptions', 'getUsoCFDIOptions');
$invoices->get('/getFiscal/{id:[0-9]+}', 'getFiscalData');
$invoices->get('/getPagos/{id:[0-9]+}', 'getPagos');
$invoices->get('/getHistory/{id:[0-9]+}', 'getHistory');
$invoices->get('/keepCheckingPayments/{id:[0-9]+}', 'keepCheckingPayments');
$invoices->get('/pagos/{id:[0-9]+}/{serie}', 'pagos');
$invoices->get('/nextFolio/{id:[0-9]+}/{serie}', 'getNextFolio');
$invoices->post('/pag', 'getInvoicesByPagination');
$invoices->post('/pag_payments', 'getInvoicesByPagination_Payments');
$invoices->post('/', 'create');
$invoices->post('/{id:[0-9]+}/document-file', 'uploadDocumentFile');
$invoices->post('/getGrid', 'getGrid');
$invoices->post('/getGridPayments', 'getGridPayments');
$invoices->post('/dataFromInvoice', 'dataFromInvoice');
$invoices->post('/deletePayment', 'deletePayment');
$invoices->post('/addPayment', 'addPayment');
$invoices->post('/sendEmailInvoice', 'sendEmailInvoice');
$invoices->post('/sendEmailsPagos', 'sendEmailsPagos');
$invoices->post('/sendEmailsReport', 'sendEmailsReport');
$invoices->post('/agregarPago/{id:[0-9]+}', 'agregarPago');
$invoices->post('/getpendingPayments', 'getpendingPayments');
$invoices->post('/addMultiPayment', 'addMultiPayment');
$invoices->post('/AddFile', 'AddFile');
$invoices->get('/getPdfFromPayments/{customer}/{status}/{saledatev1}/{saledatev2}', 'getPdfFromPayments');
$invoices->get('/getPdfFromPaymentsDetails/{type}/{customer}/{status}/{saledatev1}/{saledatev2}/{branchOffices}', 'getPdfFromPaymentsDetails');
$invoices->get('/createPdfFromRemission/{customer}/{status}/{saledatev1}/{saledatev2}/{statusT}/{remision}/{factura}', 'createPdfFromRemission');
$invoices->get('/getCSVFromPayments/{customer}/{status}/{saledatev1}/{saledatev2}', 'getCSVFromPayments');
$invoices->get('/getCSVFromPaymentsDetails/{customer}/{status}/{saledatev1}/{saledatev2}/{branchOffices}', 'getCSVFromPaymentsDetails');
$invoices->get('/getCSVFromRemission/{customer}/{status}/{saledatev1}/{saledatev2}/{statusT}/{remision}/{factura}', 'getCSVFromRemission');
$invoices->put('/{id:[0-9]+}', 'update');
$invoices->put('/{id:[0-9]+}/documents-returned', 'changeDocumentsReturnedByDriver');
$invoices->put('/{id:[0-9]+}/remission', 'remission');
$invoices->put('/fiscalData/{id:[0-9]+}', 'updateFiscal');
$invoices->put('/timbrar/{id:[0-9]+}', 'timbrar');
$invoices->put('/cancelar/{id:[0-9]+}', 'cancelar');
$invoices->put('/revisarTimbrado/{id:[0-9]+}', 'revisarTimbrado');
$invoices->put('/revisarPagos/{id:[0-9]+}', 'revisarPagos');
$invoices->put('/timbrarPago/{id:[0-9]+}', 'timbrarPago');
$invoices->put('/cancelarPago/{id:[0-9]+}', 'cancelarPago');
$invoices->put('/dateDeliveredUpdate/{id:[0-9]+}', 'dateDeliveredUpdate');
$invoices->get('/{id:[0-9]+}/remissionPruebas', 'remissionPruebas');
$invoices->delete('/{id:[0-9]+}', 'delete');
$invoices->put('/file/{id:[0-9]+}', 'deleteDocument');
$invoices->delete('/borrarPago/{id:[0-9]+}', 'borrarPago');
$invoices->get('/getfile/{id:[0-9]+}', 'getFile');
$invoices->get('/getDataCalendar', 'getDataCalendar');
$invoices->post('/getDetailsForecastperClient', 'getDetailsForecastperClient');
$invoices->post('/getDetailsForecastperRem', 'getDetailsForecastperRem');
$invoices->post('/pag_payments_auxiliar', 'getInvoicesByPagination_Payments_Auxiliar');
$invoices->post('/sendNewPdfInvoiceToCustomer', 'sendNewPdfInvoiceToCustomer');
$invoices->get('/invoicesOptions', 'invoicesOptions');
$invoices->get('/getPdfFromAuxiliarPayments/{params.type}/{customer}/{status}/{saleDatev1}/{saleDatev2}', 'getPdfFromAuxiliarPayments');
$invoices->get('/getCSVFromAuxiliarPayments/{type}/{customer}/{status}/{saleDatev1}/{saleDatev2}', 'getCSVFromAuxiliarPayments');
$invoices->get('/pdfi/{id}', 'getPdfI');
$invoices->post('/cancelRemision', 'cancelRemision');
$invoices->get('/getDateCurrent', 'getDateCurrent');
$invoices->get('/invoicesTripOptions', 'invoicesTripOptions');
$app->mount($invoices);

$laminates = new LazyCtrl(LaminatesController::class, '/laminates');
$laminates->get('/', 'getLaminates');
$laminates->get('/{id:[0-9]+}', 'getLaminate');
$laminates->get('/operator/{operatorId}/{startDate}/{endingDate}/{status}', 'getLaminatesByOperator');
$laminates->get('/operator/pdf/{operatorId}/{startDate}/{endingDate}/{status}', 'getPdfLaminatesByOperator');
$laminates->get('/transaction/{id:[0-9]+}', 'getLaminatesByTransactionId');
$laminates->post('/', 'create');
$laminates->put('/{id:[0-9]+}/produce', 'produce');
$laminates->put('/{id:[0-9]+}/finish', 'finish');
$laminates->delete('/{id:[0-9]+}', 'delete');
$app->mount($laminates);

$laminateMaterials = new LazyCtrl(LaminateMaterialsController::class, '/laminate-materials');
$laminateMaterials->get('/laminate/{id:[0-9]+}', 'getLaminateMaterialsByLaminate');
$laminateMaterials->post('/', 'create');
$laminateMaterials->delete('/{id:[0-9]+}', 'delete');
$app->mount($laminateMaterials);

$laminateAdditives = new LazyCtrl(LaminateAdditivesController::class, '/laminate-additives');
$laminateAdditives->get('/laminate/{id:[0-9]+}', 'getLaminateAdditivesByLaminate');
$app->mount($laminateAdditives);

$invoiceDetails = new LazyCtrl(InvoiceDetailsController::class, '/invoice-details');
$invoiceDetails->get('/invoice/{id:[0-9]+}', 'getInvoiceDetailsByInvoiceId');
$invoiceDetails->get('/{id:[0-9]+}', 'getInvoiceDetail');
$invoiceDetails->post('/', 'create');
$invoiceDetails->delete('/{id:[0-9]+}', 'delete');
$app->mount($invoiceDetails);

$invoiceDetails = new LazyCtrl(InvoiceInBulkDetailsController::class, '/invoice-in-bulk-details');
$invoiceDetails->get('/invoice/{id:[0-9]+}', 'getInvoiceInBulkDetailsByInvoiceId');
$invoiceDetails->get('/{id:[0-9]+}', 'getInvoiceInBulkDetail');
$invoiceDetails->post('/', 'create');
$invoiceDetails->post('/editinBulksDetails', 'editinBulksDetails');
$invoiceDetails->delete('/{id:[0-9]+}', 'delete');
$app->mount($invoiceDetails);

$laminateDetails = new LazyCtrl(InvoiceLaminateDetailsController::class, '/invoice-laminate-details');
$laminateDetails->get('/invoice/{id:[0-9]+}', 'getInvoiceLaminateDetailsByInvoiceId');
$laminateDetails->get('/{id:[0-9]+}', 'getInvoiceLaminateDetail');
$laminateDetails->post('/', 'create');
$laminateDetails->post('/editLaminateDetails', 'editLaminateDetails');
$laminateDetails->delete('/{id:[0-9]+}', 'delete');
$app->mount($laminateDetails);

$drivers = new LazyCtrl(DriversController::class, '/drivers');
$drivers->get('/', 'getDrivers');
$drivers->get('/{id:[0-9]+}', 'getDriver');
$drivers->get('/options', 'getOptions');
$drivers->post('/', 'create');
$drivers->put('/{id:[0-9]+}', 'update');
$drivers->delete('/{id:[0-9]+}', 'delete');
$app->mount($drivers);

$operators = new LazyCtrl(OperatorsController::class, '/operators');
$operators->get('/', 'getOperators');
$operators->get('/{id:[0-9]+}', 'getOperator');
$operators->get('/options', 'getOptions');
$operators->post('/', 'create');
$operators->put('/{id:[0-9]+}', 'update');
$operators->delete('/{id:[0-9]+}', 'delete');
$app->mount($operators);

$documents = new LazyCtrl(DocumentsController::class, '/documents');
$documents->get('/{id:[0-9]+}', 'getAll');
$documents->post('/file/{id:[0-9]+}', 'uploadFile');
$documents->delete('/{id:[0-9]+}', 'delete');
$documents->get('/getfile/{id:[0-9]+}', 'getFile');
$documents->get('/getDocumentOfPay/{id:[0-9]+}', 'getDocumentOfPay');
$documents->get('/getFileOrderShoppingCart/{id:[0-9]+}', 'getFileOrderShoppingCart');
$app->mount($documents);

$inmediatesales = new LazyCtrl(InmediateSaleController::class, '/inmediate-sales');
$inmediatesales->put('/request/{id}', 'request');
$inmediatesales->put('/{id:[0-9]+}/approve', 'approve');
$inmediatesales->put('/{id:[0-9]+}/generate-invoice', 'generateInvoice');
$app->mount($inmediatesales);

$shoppingCarts = new LazyCtrl(ShoppingCartsController::class, '/shopping-carts');
$shoppingCarts->post('/file/{id:[0-9]+}', 'uploadFile');
$shoppingCarts->post('/file2/{id:[0-9]+}', 'uploadFile2');
$shoppingCarts->post('/file3/{id:[0-9]+}', 'uploadFile3');
$shoppingCarts->get('/file1/{id:[0-9]+}/{fileType}/download', 'downloadDocumentFileOC');
$shoppingCarts->get('/', 'getShoppingCart');
$shoppingCarts->get('/{id:[0-9]+}', 'getShoppingCart');
$shoppingCarts->get('/requested', 'getRequestedShoppingCarts');
$shoppingCarts->get('/approved', 'getApprovedShoppingCarts');
$shoppingCarts->get('/all', 'getAllShoppingCarts');
$shoppingCarts->post('/', 'create');
$shoppingCarts->post('/sendPDF', 'sendPDF');
$shoppingCarts->get('/quotationNotePDF/{id:[0-9]+}/{order}', 'getPdfquotationNote');
$shoppingCarts->get('/pdf/{id:[0-9]+}', 'getPdf');
$shoppingCarts->get('/getPdfFromShoppingCarts/{customer}/{status}/{seller}/{saledatev1}/{saledatev2}/{idrol}/{specialorder}', 'getPdfFromShoppingCarts');
$shoppingCarts->get('/getCSVFromShoppingCarts/{customer}/{status}/{seller}/{saledatev1}/{saledatev2}/{idrol}/{specialorder}', 'getCSVFromShoppingCarts');
$shoppingCarts->post('/addCommentsToCart', 'addCommentsToCart');
$shoppingCarts->post('/updateCart', 'updateCart');
$shoppingCarts->post('/changeStatus', 'changeStatus');
$shoppingCarts->post('/changeComments', 'changeComments');
$shoppingCarts->post('/memory-prices', 'memoryPrices');
$shoppingCarts->post('/getGrid', 'getGrid');
$shoppingCarts->post('/getGridByPagination', 'getGridByPagination');
$shoppingCarts->post('/getGridReport', 'getGridReport');
$shoppingCarts->put('/request/{id}', 'request');
$shoppingCarts->put('/cancel/{id}', 'cancelarPedido');
$shoppingCarts->put('/{id:[0-9]+}/approve', 'approve');
$shoppingCarts->put('/{id:[0-9]+}/generate-invoice', 'generateInvoice');
$shoppingCarts->delete('/', 'delete');
$shoppingCarts->post('/cancelShopping', 'cancelShopping');
$shoppingCarts->get('/getDataOfOrderShoppingcart/{id:[0-9]+}/{request}', 'getDataOfOrderShoppingcart');
$shoppingCarts->put('/editReeference/{id:[0-9]+}', 'editReeference');
$shoppingCarts->get('/getDataDocument/{id:[0-9]+}', 'getDataDocument');
$app->mount($shoppingCarts);

$salesReports = new LazyCtrl(SalesReportsController::class, '/sales-reports');
$salesReports->get('/', 'getShoppingCart');
$salesReports->get('/{id:[0-9]+}', 'getShoppingCart');
$salesReports->get('/requested', 'getRequestedShoppingCarts');
$salesReports->get('/approved', 'getApprovedShoppingCarts');
$salesReports->get('/all', 'getAllShoppingCarts');
$salesReports->post('/', 'create');
$salesReports->post('/sendPDF', 'sendPDF');
$salesReports->get('/pdf/{id:[0-9]+}', 'getPdf');
$salesReports->get('/getPdfFromShoppingCarts/{customer}/{status}/{seller}/{saledatev1}/{saledatev2}', 'getPdfFromShoppingCarts');
$salesReports->get('/getCSVFromShoppingCarts/{customer}/{status}/{seller}/{saledatev1}/{saledatev2}', 'getCSVFromShoppingCarts');
$salesReports->post('/addCommentsToCart', 'addCommentsToCart');
$salesReports->post('/updateCart', 'updateCart');
$salesReports->post('/changeStatus', 'changeStatus');
$salesReports->post('/changeComments', 'changeComments');
$salesReports->post('/memory-prices', 'memoryPrices');
$salesReports->post('/getGrid', 'getGrid');
$salesReports->post('/getGridReport', 'getGridReport');
$salesReports->put('/request/{id}', 'request');
$salesReports->put('/{id:[0-9]+}/approve', 'approve');
$salesReports->put('/{id:[0-9]+}/generate-invoice', 'generateInvoice');
$salesReports->delete('/', 'delete');
$app->mount($salesReports);

$shoppingCartBaleDetails = new LazyCtrl(ShoppingCartBaleDetailsController::class, '/shopping-cart-bale-details');
$shoppingCartBaleDetails->get('/', 'getShoppingCartBaleDetails');
$shoppingCartBaleDetails->get('/shopping-cart/{id:[0-9]+}', 'getShoppingCartBaleDetailsByShoppingCartId');
$shoppingCartBaleDetails->get('/without-stock', 'getShoppingCartBaleDetailsWithoutStock');
$shoppingCartBaleDetails->post('/', 'create');
$shoppingCartBaleDetails->post('/getBaleDetails', 'getBaleDetails');
$shoppingCartBaleDetails->post('/partialization', 'partialization');
$shoppingCartBaleDetails->post('/generate-production-order', 'generateProductionOrder');
$shoppingCartBaleDetails->delete('/{id:[0-9]+}', 'delete');
$app->mount($shoppingCartBaleDetails);

$shoppingCartInBulkDetails = new LazyCtrl(ShoppingCartInBulkDetailsController::class, '/shopping-cart-in-bulk-details');
$shoppingCartInBulkDetails->get('/', 'getShoppingCartInBulkDetails');
$shoppingCartInBulkDetails->get('/shopping-cart/{id:[0-9]+}', 'getShoppingCartInBulkDetailsByShoppingCartId');
$shoppingCartInBulkDetails->get('/without-stock', 'getShoppingCartInBulkDetailsWithoutStock');
$shoppingCartInBulkDetails->get('/getDate/{date}', 'getDate');
$shoppingCartInBulkDetails->post('/getinBulkDetails', 'getinBulkDetails');
$shoppingCartInBulkDetails->post('/partialization', 'partialization');
$shoppingCartInBulkDetails->post('/', 'create');
$shoppingCartInBulkDetails->delete('/{id:[0-9]+}', 'delete');
$shoppingCartInBulkDetails->delete('/deleteInbulk/{id:[0-9]+}', 'deleteInbulk');
$app->mount($shoppingCartInBulkDetails);

$shoppingCartLaminateDetails = new LazyCtrl(ShoppingCartLaminateDetailsController::class, '/shopping-cart-laminate-details');
$shoppingCartLaminateDetails->get('/', 'getShoppingCartLaminateDetails');
$shoppingCartLaminateDetails->get('/shopping-cart/{id:[0-9]+}', 'getShoppingCartLaminateDetailsByShoppingCartId');
$shoppingCartLaminateDetails->post('/getinLaminateDetails', 'getinLaminateDetails');
$shoppingCartLaminateDetails->post('/partialization', 'partialization');
$shoppingCartLaminateDetails->get('/without-stock', 'getShoppingCartLaminateDetailsWithoutStock');
$shoppingCartLaminateDetails->post('/', 'create');
$shoppingCartLaminateDetails->delete('/{id:[0-9]+}', 'delete');
$app->mount($shoppingCartLaminateDetails);

$departments = new LazyCtrl(DepartmentsController::class, '/departments');
$departments->get('/', 'getDepartments');
$departments->get('/{id:[0-9]+}', 'getDepartment');
$departments->get('/options', 'getOptions'); 
$departments->post('/', 'create');
$departments->put('/{id:[0-9]+}', 'update'); 
$departments->delete('/{id:[0-9]+}', 'delete'); 
$app->mount($departments);

$areas = new LazyCtrl(AreasController::class, '/areas');
$areas->get('/', 'getAreas');
$areas->get('/{id:[0-9]+}', 'getArea');
$areas->get('/options', 'getOptions');
$areas->get('/options/department/{id:[0-9]+}', 'getOptionsByDepartmentId');
$areas->post('/', 'create');
$areas->put('/{id:[0-9]+}', 'update');
$areas->delete('/{id:[0-9]+}', 'delete');
$app->mount($areas);

$positions = new LazyCtrl(PositionsController::class, '/positions');
$positions->get('/', 'getPositions');
$positions->get('/{id:[0-9]+}', 'getPosition');
$positions->get('/options', 'getOptions');
$positions->post('/', 'create');
$positions->post('/pag', 'getPositionsByPagination');
$positions->put('/{id:[0-9]+}', 'update');
$positions->delete('/{id:[0-9]+}', 'delete');
$app->mount($positions);

$bom = new LazyCtrl(BomController::class, '/bom');
$bom->get('/{id:[0-9]+}', 'getBom');
$bom->get('/options', 'getOptions');
$bom->get('/productBom/{id:[0-9]+}', 'productBom');
$bom->post('/', 'create');
$bom->put('/{id:[0-9]+}', 'update');
$bom->delete('/{id:[0-9]+}', 'delete');
$app->mount($bom);

$timetables = new LazyCtrl(TimetablesController::class, '/timetables');
$timetables->get('/', 'getTimetables');
$timetables->get('/{id:[0-9]+}', 'getTimetable');
$timetables->get('/options', 'getOptions');
$timetables->post('/', 'create');
$timetables->post('/pag', 'getTimetablesByPagination');
$timetables->put('/{id:[0-9]+}', 'update');
$timetables->delete('/{id:[0-9]+}', 'delete');
$app->mount($timetables);

$shifts = new LazyCtrl(ShiftsController::class, '/shifts');
$shifts->get('/', 'getShifts');
$shifts->get('/{id:[0-9]+}', 'getShift');
$shifts->get('/options', 'getOptions'); 
$shifts->post('/', 'create');
$shifts->put('/{id:[0-9]+}', 'update'); 
$shifts->delete('/{id:[0-9]+}', 'delete'); 
$app->mount($shifts);

$employees = new LazyCtrl(EmployeesController::class, '/employees');
$employees->get('/', 'getEmployees');
$employees->get('/{id:[0-9]+}', 'getEmployee');
$employees->get('/options', 'getOptions'); 
$employees->post('/', 'create');
$employees->put('/{id:[0-9]+}', 'update'); 
$employees->delete('/{id:[0-9]+}', 'delete'); 
$app->mount($employees);


$works = new LazyCtrl(HandiWorkController::class, '/works');
$works->get('/', 'getHandiWorks');
$works->get('/{id:[0-9]+}', 'getHandiWork');
$works->get('/options', 'getOptions');
$works->post('/', 'create');
$works->put('/{id:[0-9]+}', 'update');
$works->delete('/{id:[0-9]+}', 'delete');
$app->mount($works);

$worksProducts = new LazyCtrl(HandiWorkProductsController::class, '/works-products');
$worksProducts->get('/getby/{id:[0-9]+}', 'getby');
$worksProducts->get('/', 'getHandiWorks');
$worksProducts->get('/{id:[0-9]+}', 'getHandiWork');
$worksProducts->get('/options', 'getOptions');
$worksProducts->post('/', 'create');
$worksProducts->put('/{id:[0-9]+}', 'update');
$worksProducts->delete('/{id:[0-9]+}', 'delete');
$app->mount($worksProducts);

$worksProducts = new LazyCtrl(HandiWorkLotsController::class, '/works-lots');
$worksProducts->get('/getby/{id:[0-9]+}', 'getbyLots');
$worksProducts->get('/', 'getHandiWorksLots');
$worksProducts->get('/{id:[0-9]+}', 'getHandiWorklot');
$worksProducts->get('/options', 'getOptions');
$worksProducts->post('/', 'create');
$worksProducts->put('/{id:[0-9]+}', 'update');
$worksProducts->delete('/{id:[0-9]+}', 'delete');
$app->mount($worksProducts);

$holidays = new LazyCtrl(HolidaysController::class, '/holidays');
$holidays->get('/', 'getHolidays');
$holidays->get('/{id:[0-9]+}', 'getHoliday');
$holidays->get('/options', 'getOptions'); 
$holidays->post('/', 'create');
$holidays->put('/{id:[0-9]+}', 'update'); 
$holidays->delete('/{id:[0-9]+}', 'delete'); 
$app->mount($holidays);

$vacations = new LazyCtrl(VacationsController::class, '/vacations');
$vacations->get('/', 'getVacations');
$vacations->get('/vacationRequest', 'getVacationRequest');
$vacations->get('/vacationRequestTrue', 'getVacationRequestTrue');
$vacations->get('/vacationRequestFilter/{id:[0-9]+}', 'getVacationRequestFilter');
$vacations->get('/vacationRequestFilterTrue/{id:[0-9]+}', 'getVacationRequestFilterTrue');
$vacations->get('/{id:[0-9]+}', 'getVacation');
$vacations->get('/options', 'getOptions'); 
$vacations->post('/', 'create');
$vacations->post('/request', 'createRequest');
$vacations->put('/approve-request/{id:[0-9]+}', 'approveRequest');
$vacations->put('/{id:[0-9]+}', 'update'); 
$vacations->delete('/{id:[0-9]+}', 'delete'); 
$vacations->delete('/deniedRequest/{id:[0-9]+}', 'deleteRequest'); 
$app->mount($vacations);

$incidencias = new LazyCtrl(IncidenciasController::class, '/incidencias');
$incidencias->get('/', 'getIncidencias');
$incidencias->get('/{id:[0-9]+}', 'getIncidencia');
$incidencias->get('/options', 'getOptions'); 
$incidencias->post('/', 'create');
$incidencias->put('/{id:[0-9]+}', 'update'); 
$incidencias->delete('/{id:[0-9]+}', 'delete'); 
$app->mount($incidencias);

$incidenciasCapture = new LazyCtrl(IncidenciasCaptureController::class, '/capture-incidencias');
$incidenciasCapture->get('/{id:[0-9]+}', 'getIncidencias');
$incidenciasCapture->get('/vacations/{id:[0-9]+}', 'getIncidenciasVacations');
$incidenciasCapture->post('/', 'create'); 
$app->mount($incidenciasCapture);

$notes = new LazyCtrl(NotesController::class, '/notes');
$notes->get('/{id:[0-9]+}', 'getNotes');
$notes->post('/', 'create');
$notes->delete('/{id:[0-9]+}', 'delete');
$app->mount($notes);

$ranges = new LazyCtrl(RangesController::class, '/ranges');
$ranges->get('/', 'getRanges');
$ranges->get('/{id:[0-9]+}', 'getRange');
$ranges->get('/options', 'getOptions');
$ranges->post('/', 'create');
$ranges->put('/{id:[0-9]+}', 'update');
$ranges->delete('/{id:[0-9]+}', 'delete');
$ranges->get('/getStateOptions', 'getStateOptions');
$ranges->get('/getMunicipalityOptions', 'getMunicipalityOptions');
$app->mount($ranges);

$expensesType = new LazyCtrl(ExpensesTypeController::class, '/expenses');
$expensesType->get('/', 'getExpenses');
$expensesType->get('/{id:[0-9]+}', 'getExpense');
$expensesType->get('/options', 'getOptions');
$expensesType->post('/', 'create');
$expensesType->put('/{id:[0-9]+}', 'update');
$expensesType->delete('/{id:[0-9]+}', 'delete');
$app->mount($expensesType);

$trips = new LazyCtrl(TripsController::class, '/trips');
$trips->get('/', 'getTrips');
$trips->get('/{id:[0-9]+}', 'getTrip');
$trips->post('/', 'create');
$trips->put('/{id:[0-9]+}', 'update');
$trips->put('/status/{id:[0-9]+}', 'updateStatus');
$trips->delete('/{id:[0-9]+}', 'delete');
$trips->post('/driver', 'addDriver');
$trips->get('/drivers/{id:[0-9]+}', 'getDrivers');
$trips->delete('/driver/{id:[0-9]+}', 'deleteDriver');
$trips->get('/timbrar/{id:[0-9]+}', 'invoicingPorterage');
$trips->put('/revisarTimbrado/{id:[0-9]+}', 'checkInvoice');
$trips->put('/cancelar/{id:[0-9]+}', 'cancelar');
$app->mount($trips);

$vehicleType = new LazyCtrl(VehicleTypeController::class, '/vehicleType');
$vehicleType->get('/', 'getVehicles');
$vehicleType->get('/{id:[0-9]+}', 'getVehicle');
$vehicleType->get('/options', 'getOptions');
$vehicleType->get('/optionstowing', 'getOptionsTowing');
$vehicleType->get('/optionsconfig', 'getOptionsConfig');
$vehicleType->post('/', 'create');
$vehicleType->put('/{id:[0-9]+}', 'update');
$vehicleType->delete('/{id:[0-9]+}', 'delete');
$app->mount($vehicleType);

$vehicleType = new LazyCtrl(VehicleController::class, '/vehicle');
$vehicleType->get('/', 'getVehicles');
$vehicleType->get('/{id:[0-9]+}', 'getVehicle');
$vehicleType->get('/options', 'getOptions');
$vehicleType->get('/vehicle-data/{id:[0-9]+}', 'getVehicleData');
$vehicleType->post('/', 'create');
$vehicleType->put('/{id:[0-9]+}', 'update');
$vehicleType->delete('/{id:[0-9]+}', 'delete');
$app->mount($vehicleType);

$state = new LazyCtrl(StatesController::class, '/state');
$state->get('/', 'getStates');
$state->get('/{id:[0-9]+}', 'getState');
//$state->get('/options', 'getOptions'); //Las opciones de estados estan en /ranges
$state->post('/', 'create');
$state->put('/{id:[0-9]+}', 'update');
$state->delete('/{id:[0-9]+}', 'delete');
$app->mount($state);

$municipality = new LazyCtrl(MunicipalityController::class, '/municipality');
$municipality->get('/', 'getMunicipalitys');
$municipality->get('/{id:[0-9]+}', 'getMunicipality');
//$state->get('/options', 'getOptions'); //Las ciudades de estados estan en /ranges
$municipality->post('/', 'create');
$municipality->put('/{id:[0-9]+}', 'update');
$municipality->delete('/{id:[0-9]+}', 'delete');
$app->mount($municipality);

$tripExoenses = new LazyCtrl(TripExpensesController::class, '/trip-expenses');
$tripExoenses->get('/all/{id:[0-9]+}', 'getExpenses');
$tripExoenses->get('/{id:[0-9]+}', 'getExpense');
$tripExoenses->post('/', 'create');
$tripExoenses->put('/{id:[0-9]+}', 'update');
$tripExoenses->delete('/{id:[0-9]+}', 'delete');
$tripExoenses->post('/file/expense/{id:[0-9]+}', 'createFileExpense'); 
$app->mount($tripExoenses);

$shippings = new LazyCtrl(ShippingsController::class, '/shippings');
$shippings->get('/all/{id:[0-9]+}', 'getShippings');
$shippings->get('/{id:[0-9]+}', 'getShipping');
$shippings->get('/folio/{id:[0-9]+}', 'getFolio');
$shippings->get('/folioShipping/{id:[0-9]+}', 'getFolioShipping');
$shippings->post('/', 'create');
$shippings->put('/{id:[0-9]+}', 'update');
$shippings->delete('/{id:[0-9]+}', 'delete');
$shippings->post('/file/shipping/{id:[0-9]+}', 'createFileShipping'); 
$app->mount($shippings);

$shippingDetails = new LazyCtrl(ShippingDetailsController::class, '/shipping-details');
$shippingDetails->get('/all/{id:[0-9]+}', 'getShippingDetails');
$shippingDetails->get('/{id:[0-9]+}', 'getShippingProduct');
$shippingDetails->get('/options', 'getOptions');
$shippingDetails->post('/', 'create');
$shippingDetails->put('/{id:[0-9]+}', 'update');
$shippingDetails->delete('/{id:[0-9]+}', 'delete');
$shippingDetails->get('/productInventoryOptions/{branchOfficeId}/{storageId}/{categoryId}/{lineId}/{productId}/{date}', 'getProductInventory');
$app->mount($shippingDetails);

$incentivesProduction = new LazyCtrl(ProductionIncentivesController::class, '/incentives-production');
$incentivesProduction->get('/', 'getIncentives');
$incentivesProduction->get('/{employeeId}/{dateStart}/{dateEnd}', 'getIncentivesFilter');
$incentivesProduction->get('/csv/{employeeId}/{dateStart}/{dateEnd}', 'getIncentivesCsv');
$incentivesProduction->get('/pdf/{employeeId}/{dateStart}/{dateEnd}', 'getIncentivesPdf');
$app->mount($incentivesProduction);

$repositories = new LazyCtrl(RepositoriesController::class, '/repositories');
$repositories->get('/', 'getAll');
$repositories->get('/getByParent/{id:[0-9]+}', 'getByParent');
$repositories->get('/{id:[0-9]+}', 'get');
$repositories->post('/', 'create');
$repositories->put('/{id:[0-9]+}', 'update');
$repositories->delete('/{id:[0-9]+}', 'delete');
$repositories->get('/getMenus', 'getMenus');
$app->mount($repositories);

$account = new LazyCtrl(AccountTradeController::class, '/account-trade');
$account->get('/', 'getAccounts');
$account->get('/{id:[0-9]+}', 'getAccount');
$account->get('/options', 'getOptions');
$account->post('/', 'create');
$account->put('/{id:[0-9]+}', 'update');
$account->delete('/{id:[0-9]+}', 'delete');
$app->mount($account);

$outputType = new LazyCtrl(OutputsTypesController::class, '/output_type');
$outputType->get('/', 'getOutputs');
$outputType->get('/{id:[0-9]+}', 'getOutput');
$outputType->get('/options', 'getOptions');
$outputType->post('/', 'create');
$outputType->put('/{id:[0-9]+}', 'update');
$outputType->delete('/{id:[0-9]+}', 'delete');
$app->mount($outputType);

$movementTrade = new LazyCtrl(MovementTradeController::class, '/movement-trade');
$movementTrade->get('/', 'getMovements');
$movementTrade->get('/{id:[0-9]+}', 'getMovement');
$movementTrade->get('/options', 'getOptions');
$movementTrade->post('/', 'create');
$movementTrade->put('/{id:[0-9]+}', 'update');
$movementTrade->delete('/{id:[0-9]+}', 'delete');
$movementTrade->post('/getMovements', 'getMovementsPost');
$movementTrade->get('/optionsYears', 'getOptionsYears');
$app->mount($movementTrade);

$dashboardSales = new LazyCtrl(DashboardSalesController::class, '/dashboard-sales');
$dashboardSales->get('/daySales', 'daySales');
$dashboardSales->get('/weekSales', 'weekSales');
$dashboardSales->get('/monthSales', 'monthSales');
$dashboardSales->get('/dayPays', 'dayPays');
$dashboardSales->get('/weekPays', 'weekPays');
$dashboardSales->get('/monthPays', 'monthPays');
$dashboardSales->get('/yearPays', 'yearPays');
$dashboardSales->get('/daySalesCharts', 'daySalesCharts');
$dashboardSales->get('/weekSalesCharts', 'weekSalesCharts');
$dashboardSales->get('/yearSalesCharts', 'yearSalesCharts');
$dashboardSales->get('/weekSellerSalesCharts', 'weekSellerSalesCharts');
$dashboardSales->get('/monthSellerSalesCharts', 'monthSellerSalesCharts');
$dashboardSales->get('/monthSellerBoxesCharts', 'monthSellerBoxesCharts');
$dashboardSales->get('/top10BestCustomers', 'top10BestCustomers');
$app->mount($dashboardSales);


$dashboardBacks = new LazyCtrl(DashboardMovementTradeController::class, '/dashboard-backs');
$dashboardBacks->get('/getGpiOne', 'getGpiOne');
$dashboardBacks->get('/getGpiTwo', 'getGpiTwo');
$dashboardBacks->get('/getChartOne', 'getChartOne');
$dashboardBacks->get('/getChartTwo', 'getChartTwo');
$dashboardBacks->get('/getChartTwoByPayment', 'getChartTwoByPayment');
$dashboardBacks->get('/getChartThree', 'getChartThree');
$app->mount($dashboardBacks);

$marks = new LazyCtrl(MarksController::class, '/marks');
$marks->get('/', 'getLines');
$marks->get('/{id:[0-9]+}', 'getLine');
$marks->get('/options', 'getOptions');
$marks->get('/options/category/{id:[0-9]+}', 'getOptionsByCategoryId');
$marks->post('/', 'create');
$marks->put('/{id:[0-9]+}', 'update');
$marks->delete('/{id:[0-9]+}', 'delete');
$app->mount($marks);

$equivalence = new LazyCtrl(EquivalenceController::class, '/equivalence');
$equivalence->get('/getbyProduct/{id:[0-9]+}', 'getbyProduct');
$equivalence->get('/get/{id:[0-9]+}/{id2:[0-9]+}', 'get');
$equivalence->get('/options', 'getOptions');
$equivalence->get('/productBom/{id:[0-9]+}', 'productBom');
$equivalence->post('/', 'create');
$equivalence->put('/EditEquivalence', 'update');
$equivalence->put('/{id:[0-9]+}', 'update');
$equivalence->delete('/{id:[0-9]+}', 'delete');
$app->mount($equivalence);

$ticket = new LazyCtrl(TicketController::class, '/ticket');
$ticket->get('/crearTicket/{id:[0-9]+}/{user_id:[0-9]+}', 'crearTicket');
$app->mount($ticket);

$municipios = new LazyControllerCollection( StatesMunicipalityController::class, '/municipios');
$municipios->get('/', 'getAll');
$municipios->get('/options', 'getOptions');
$municipios->get('/getEstadosOptions', 'getEstadosOptions');
$municipios->get('/getMunicipiosbyEstadosOptions/{id:[0-9]+}', 'getMunicipiosbyEstadosOptions');
$municipios->get('/{id:[0-9]+}', 'get');
$municipios->post('/', 'create');
$municipios->put('/{id:[0-9]+}', 'update');
$municipios->delete('/{id:[0-9]+}', 'delete');
$app->mount($municipios);

$roles = new LazyControllerCollection( RolesController::class, '/roles');
$roles->get('/', 'getAll');
$roles->get('/{id:[0-9]+}', 'getRol');
$roles->post('/', 'create');
$roles->put('/{id:[0-9]+}', 'update');
$roles->delete('/{id:[0-9]+}', 'delete');
$app->mount($roles);


$minimumstock = new LazyCtrl(ProductsStockMinimunController::class, '/minimum-stock');
$minimumstock->get('/getbyProduct/{id:[0-9]+}', 'getbyProduct');
$minimumstock->get('/get/{id:[0-9]+}/{id2:[0-9]+}', 'get');
$minimumstock->get('/options', 'getOptions');
$minimumstock->get('/productBom/{id:[0-9]+}', 'productBom');
$minimumstock->post('/', 'create');
$minimumstock->put('/EditEquivalence', 'update');
$minimumstock->put('/{id:[0-9]+}', 'update');
$minimumstock->delete('/{id:[0-9]+}', 'delete');
$app->mount($minimumstock);

$tripdestination = new LazyCtrl(TripDestinationsController::class, '/trip-destination');
$tripdestination->post('/', 'create');
$tripdestination->get('/getbytrip/{id:[0-9]+}', 'getbyTrip');
$tripdestination->put('/{id:[0-9]+}', 'update');
$tripdestination->delete('/{id:[0-9]+}', 'delete');
$app->mount($tripdestination);

$reports = new LazyCtrl(ReportsController::class, '/reports');
$reports->post('/pagbyclients', 'pagbyclients');
$reports->post('/pagbySellers', 'pagbySellers');
$reports->get('/getCsvpagbyclients/{dateOf}/{DateUntil}/{sucursal}/{marca}/{product}/{linea}/{cliente}', 'getCsvpagbyclients');
$reports->get('/getCsvpagbySeller/{dateOf}/{DateUntil}/{sucursal}/{marca}/{product}/{linea}/{seller}/{client}', 'getCsvpagbySeller');
$reports->get('/getPdfpagbyclients/{dateOf}/{DateUntil}/{sucursal}/{marca}/{product}/{linea}/{cliente}', 'getPdfpagbyclients');
$reports->get('/getPdfpagbySeller/{dateOf}/{DateUntil}/{sucursal}/{marca}/{product}/{linea}/{seller}/{client}', 'getPdfpagbySellers');
$app->mount($reports);

$supercluster = new LazyControllerCollection(SuperclusterController::class, '/supercluster');
$supercluster->get('/getAll', 'getAll');
$supercluster->get('/getOptions', 'getOptions');
$supercluster->get('/getById/{id:[0-9]+}', 'getById');
$supercluster->post('/create', 'create');
$supercluster->put('/update/{id:[0-9]+}', 'update');
$supercluster->delete('/delete/{id:[0-9]+}', 'delete');
$app->mount($supercluster);
