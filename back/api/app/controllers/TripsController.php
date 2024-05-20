<?php

use Phalcon\Mvc\Controller;

class TripsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];
    public $batuta_url = '';

    public function onConstruct()
    {
        $this->batuta_url = ($_SERVER['SERVER_NAME'] === 'api_alpez.wasp.mx' ? 'https://batuta.wasp.mx' : 'http://batuta.beta.antfarm.mx');
    }

    public function getTrips ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT t.id, t.folio,t.invoice_id, c.name as customer, to_char(t.trip_date,'DD/MM/YYYY HH:mm:ss') AS date, 
            v.economic_number, v.vehicle_brand AS brand, v.vehicle_model AS model, vt.name AS type, bo.name as sucursal,
            t.status_timbrado
            FROM log_trips AS t
            INNER JOIN log_vehicle AS v ON v.id = t.vehicle_id
            INNER JOIN log_vehicle_type AS vt ON vt.id = v.type_id
            INNER JOIN sls_invoices i on i.id = t.invoice_id
            INNER JOIN sls_customer_branch_offices o on o.id = i.customer_branch_office_id
            INNER JOIN sls_customers c on c.id = o.customer_id
            INNER JOIN sls_shopping_cart sc on sc.id = i.shopping_cart_id
            INNER JOIN wms_branch_offices bo on bo.id = sc.branchoffice
            ORDER BY t.folio ASC";
            $trips = $this->db->query($sql)->fetchAll();
            $this->content['trips'] = $trips;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getTrip ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT t.id, t.folio, to_char(t.trip_date,'DD/MM/YYYY HH:mm:ss') AS date, v.economic_number, v.id AS vehicle_id, t.status,
            t.invoice_id, i.id || ' / ' || i.serie || '-' ||i.folio_fiscal as invoice,t.status_timbrado,t.uuid,t.id_request,t.message,
            t.folio||'_'||(case when ssc.branchoffice = 9 then 'BRB780222GD3' when ssc.branchoffice = 12 then 'LOTG541005G9A' else 'RRM010601UV1' end)||'.pdf' as pdf
            FROM log_trips AS t
            INNER JOIN log_vehicle AS v ON v.id = t.vehicle_id
            INNER JOIN log_vehicle_type AS vt ON vt.id = v.type_id
            INNER JOIN sls_invoices i on i.id = t.invoice_id
            INNER JOIN sls_shopping_cart ssc on ssc.id = i.shopping_cart_id
            WHERE t.id = $id
            ORDER BY t.folio ASC";

            $this->content['trip'] = $this->db->query($sql)->fetch();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $yearFolio = date('Y');

                $folioOut = Trips::findFirst(array("order" => "id DESC"));
                $this->content['out'] = $folioOut;
                if (!$folioOut){
                    $folioIn = ($yearFolio * 100000);
                }
                if ($folioOut) {
                    $folioIn = ($folioOut->folio) + 1 ;
                }
                $trip = new Trips();
                $trip->setTransaction($tx);
                $trip->folio = $folioIn;
                $trip->invoice_id = $request['invoice_id'];
                $trip->trip_date = $request['date'];
                $trip->vehicle_id = $request['vehicle_id'];
                $trip->status = 'NUEVO';
                $trip->account_id = $actualAccount;
                if ($trip->create()) {
                    $this->content['result'] = true;
                    $this->content['id'] = $trip->id;
                    $this->content['message'] = Message::success('El Embarque ha sido creado.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($trip);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Embarque.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $trip = Trips::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($trip) {
                    $trip->setTransaction($tx);
                    $trip->driver_id = $request['driver'];
                    $trip->destiny_id = $request['destiny'];
                    $trip->trip_date = $request['date'];
                    $trip->economic_number = $request['economic_number'];
                    $trip->account_id = $actualAccount;
                    if ($trip->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Embarque ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($trip);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Embarque.');
                        $tx->rollback();
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function updateStatus ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $trip = Trips::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $date = new DateTime();

                if ($trip) {
                    $trip->setTransaction($tx);
                    $trip->status = $request['status'];
                    $trip->account_id = $actualAccount;
                    if ($request['status'] == 'EN TRÁNSITO') { 
                        $movement = new Movements();
                        $movement->setTransaction($tx);
                        $movement->storage_id = 34;
                        $movement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                        $movement->type_id = 2;
                        $movement->po_id = null;
                        $movement->status = 'EJECUTADO';
                        $movement->date = $date->format('Y-m-d') ." ". $date->format('h:i:s');
                        $movement->ejecute_date = date("Y-m-d H:i:s");
                        if ($movement->create()) {
                            $shippings = Shippings::find("trip_id = $id");
                            foreach ($shippings as $s => $value) {
                                $shippingsDetails = ShippingDetails::find(array("shipping_id = ".$shippings[$s]->id));
                                foreach ($shippingsDetails as $sd => $value) {
                                    $this->content['message'] = $shippingsDetails[$sd];
                                    $movementDetail = new MovementDetails();
                                    $tx = $this->transactions->get();
                                    $movementDetail->setTransaction($tx);
                                    $movementDetail->movement_id = $movement->id;
                                    $movementDetail->product_id = $shippingsDetails[$sd]->product_id;
                                    $movementDetail->qty = $shippingsDetails[$sd]->qty;
                                    $movementDetail->create();
                                }
                                if ($trip->update()) {
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('El Estatus ha cambiado a: '.$request['status']);
                                    $tx->commit();
                                } else {
                                    $this->content['error'] = Helpers::getErrors($trip);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Estatus.');
                                    $tx->rollback();
                                }
                            }
                        }
                    } else{
                        if ($trip->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Estatus ha cambiado a: '.$request['status']);
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($trip);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Estatus.');
                            $tx->rollback();
                        }
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $trip = Trips::findFirst($id);

                if ($trip) {
                    $trip->setTransaction($tx);
                    $trip->TripDestinations->delete();
                    if ($trip->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Emabarque ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($trip);
                        if (isset($this->content['error']['message'])) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Emabarque.');
                        }
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Emabarque no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function addDriver ()
    {
        try {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $validUser = Auth::getUserData($this->config);
            if ($this->userHasPermission()) {
                $driver = TripDrivers::findFirst("trip_id = ". $request['trip_id']. " and driver_id = ".$request['operator_id']);
                if(!$driver){
                    $driver = new TripDrivers();
                    $driver->setTransaction($tx);
                    $driver->trip_id = $request['trip_id'];
                    $driver->driver_id = $request['operator_id'];

                    $driver->created_by = $validUser->id;

                    if ($driver->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El operador ha sido agregado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($driver);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el operador.');
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El operador ya ha sido agregado anteriormente.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);

    }

    public function getDrivers ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT td.id, d.name, d.rfc, d.license
                    FROM log_trip_drivers td
                    JOIN wms_drivers d on d.id = td.driver_id
                    where td.trip_id = $id";

            $this->content['drivers'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function deleteDriver ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $driver = TripDrivers::findFirst("id = $id");

                if ($driver) {
                    $driver->setTransaction($tx);
                    if ($driver->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Operador ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($trip);
                        if (isset($this->content['error']['message'])) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Operador.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('El Operador no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function invoicingPorterage ($id ) {
        try {
            if ($this->userHasPermission()) {
                // Informacion de la empresa en este caso: TF
                $id_batuta = 80;
                
                // $invoice = new Invoice();
                $porterage = Trips::findFirst($id); // Data de la Remision
                $invoice = Invoices::findFirst($porterage->invoice_id); // Datos de la carta porte
                // $driver = Drivers::findFirst($invoice->driver_id); // Datos del Conductor
                $vehicle = Vehicle::findFirst($porterage->vehicle_id); // Datos del Vehiculo
                $branchOffice = $invoice->ShoppingCart->BranchOffice;
                if($porterage->status_timbrado != 0 && $porterage->status_timbrado != 6){
                    $this->content['result'] == 'success';
                    return;
                }
                $issuing_rfc = '';
                $issuing_client = '';
                $issuing_place = '';
                switch ($invoice->ShoppingCart->branchoffice) {
                    case 9:
                        $id_batuta = 80;
                        $issuing_rfc = 'BRB780222GD3';
                        $issuing_client = 'BALEROS RETENES Y BANDAS DE CLIENTE DURANGO SA DE CV ';
                        $issuing_place = '34000';
                        break;
                    case 13:
                        $id_batuta = 82;
                        $issuing_rfc = 'RRM010601UV1';
                        $issuing_client = 'REBASA RODAMIENTOS Y MANGUERAS S CLIENTE DE RL DE CV ';
                        $issuing_place = '34229';
                        break;
                    case 12:
                        $id_batuta = 81;
                        $issuing_rfc = 'LOTG541005G9A';
                        $issuing_client = 'GUILLERMO LOPEZ DE LARA TINAJERO';
                        $issuing_place = '34240';
                        break;
                    case 14:
                        $id_batuta = 82;
                        $issuing_rfc = 'RRM010601UV1';
                        $issuing_client = 'REBASA RODAMIENTOS Y MANGUERAS S CLIENTE DE RL DE CV ';
                        $issuing_place = '34229';
                        break;
                    default:
                        $id_batuta = 80;
                        $issuing_rfc = 'BRB780222GD3';
                        $issuing_client = 'BALEROS RETENES Y BANDAS DE CLIENTE DURANGO SA DE CV ';
                        $issuing_place = '34000';
                        break;
                }
                // DUDA 1. se van a timbrar los traspasos ? de ser asi cambiara el seteo en el data['folio'] al id del traspaso de sucursal
                $data = [];

                $data['company_id'] = $id_batuta;
                $data['folio'] = $porterage->folio;
                $data['expedition_date'] = date('Y-m-d') . 'T' . date('H:i:s', strtotime(date("H:i:s") . " -1 hour"));
                $data['expedition_place'] = $issuing_place;
                $data['rfc'] = $issuing_rfc;
                $data['name'] = $issuing_client;

                $data['concepts']  = [];

                $isSale = true;  // condicion para saber si es una venta o un traspaso de sucursal, se modificara si la duda 1. se resuelve

                if ($isSale) {
                    if ($invoice) {
                        // Realizamos la peticion de acuerdo al tipo de producto de venta
                        $cpt = [];
                        $bales_detail = $invoice->InvoiceDetails;
                        if ($bales_detail) {
                            foreach ($bales_detail as $detail) {
                                $cpt = [];
                                $cpt['descripcion'] =  $detail->Bales->Products->name;
                                $cpt['claveProdServ'] = $detail->Bales->Products->clave_producto_id;
                                $cpt['unidad'] = 'KILOGRAMOS';
                                $cpt['claveUnidad'] = 'KGM';
                                $cpt['cantidad'] = $detail->Bales->qty;
                                $cpt['valorUnitario'] = $detail->unit_price;
                                $cpt['importe'] = floatval($detail->qty * $detail->unit_price);
                                $data['conceptos'][] = $cpt;
                            }
                        }
                        $inBulk_detail = $invoice->InvoiceInBulkDetails;
                        if ($inBulk_detail) {
                            foreach ($inBulk_detail as $detail) {
                                $cpt = [];
                                $cpt['descripcion'] =  $detail->Products->name;
                                $cpt['claveProdServ'] = $detail->Products->clave_producto_id;
                                $cpt['unidad'] = 'KILOGRAMOS';
                                $cpt['claveUnidad'] = 'KGM';
                                $cpt['cantidad'] = $detail->qty;
                                $cpt['valorUnitario'] = $detail->unit_price;
                                $cpt['importe'] = floatval($detail->qty * $detail->unit_price);
                                $data['conceptos'][] = $cpt;
                            }
                        }
                        $laminate_detail = $invoice->InvoiceLaminateDetails;
                        if ($laminate_detail) {
                            foreach ($laminate_detail as $detail) {
                                $cpt = [];
                                $cpt['descripcion'] =  $detail->WmLaminates->Products->name ?? $detail->Products->name;
                                $cpt['claveProdServ'] = $detail->WmLaminates->Products->clave_producto_id ?? $detail->Products->clave_producto_id;
                                $cpt['unidad'] = 'KILOGRAMOS';
                                $cpt['claveUnidad'] = 'KGM';
                                $cpt['cantidad'] = $detail->qty;
                                $cpt['valorUnitario'] = $detail->unit_price;
                                $cpt['importe'] = floatval($detail->qty * $detail->unit_price);
                                $data['conceptos'][] = $cpt;
                            }
                        }
                    }
                }

                $cartaPorte = [];
                $cartaPorte['transpInternac'] = 'No';   // Se refiere al transporte internacional Si : No
                $cartaPorte['totalDistRec'] = 0;    // En kilometros

                $ubicaciones = []; // Origen y Destino
                $departure = strtotime($porterage->trip_date);
                //$delivery = strtotime($porterage->delivery_date);

                $departure_date = date('Y-m-d', $departure) . 'T' . date('H:i:s', $departure);
                //$delivery_date = date('Y-m-d', $delivery) . 'T' . date('H:i:s', $delivery);

                // $cp = new Porterage();
                // $odd_information = $cp->getLocations($porterage->invoice_id);   // Origin, Destiny And Driver Infomration

                // ORIGEN
                $origin_storage_id = 'OR' . sprintf('%06s', $porterage->id); // OR de ORIGEN => OR123456

                $origin['tipoUbicacion'] = 'Origen';                         // Origen
                $origin['iDUbicacion'] = $origin_storage_id;
                $origin['rfcRemitenteDestinatario'] = $issuing_rfc;          // ??
                $origin['nombreRFC'] = $issuing_client;                      // ??
                $origin['fechaHoraSalidaLlegada'] = $departure_date;
                $origin['distanciaRecorrida'] = 0;   // Cero
                $origin['domicilio'] = [
                    'calle' => $branchOffice->address,
                    'numeroExterior' => $branchOffice->outdoor_number,
                    'numeroInterior' => $branchOffice->indoor_number,
                    'referencia' => $branchOffice->between_street,
                    'codigoPostal' => $branchOffice->PostalCode->postal_code,
                    'colonia' => $branchOffice->Suburb->suburb_code,
                    'localidad' => $branchOffice->PostalCode->location_code,
                    'municipio' => $branchOffice->PostalCode->municipality_code,
                    'estado' => $branchOffice->PostalCode->state_code,
                    'pais' => $branchOffice->PostalCode->State->country_code
                ];

                // DESTINY
                $destiny_locations = [];
                $totalDistRec = 0;
                $destinations = TripDestinations::find("trip_id = $id");
                foreach ($destinations as $addr) {
                    // Destino si es venta es Cliente, si es traspaso es misma empresa
                    $destiny_storage_id = 'DE' . sprintf('%06s', $porterage->id); // DE de DESTINO => DE123456
                    $delivery_date = date('Y-m-d', strtotime($addr->date)) . 'T' . date('H:i:s', strtotime($addr->date));

                    $destiny['tipoUbicacion'] = 'Destino';                      // Destino
                    $destiny['iDUbicacion'] = $destiny_storage_id;
                    $destiny['fechaHoraSalidaLlegada'] = $delivery_date;
                    $destiny['distanciaRecorrida'] =  $addr->distance ?? 0; // Aqui si se pone la distancia
                    $totalDistRec += floatval($addr->distance);

                    $destiny['domicilio'] = [
                        'calle' => $addr->street,
                        'numeroExterior' => $addr->outdoor_number,
                        'numeroInterior' => $addr->indoor_number,
                        'codigoPostal' => $addr->PostalCode->postal_code,
                        'referencia' => $addr->between_street,
                        'colonia' => $addr->Suburb->suburb_code,
                        'localidad' => $addr->PostalCode->location_code,
                        'municipio' => $addr->PostalCode->municipality_code,
                        'estado' => $addr->PostalCode->state_code,
                        'pais' => $addr->PostalCode->State->country_code,
                    ];

                    if ($isSale) {
                        $destiny['rfcRemitenteDestinatario'] = $invoice->CustomerTaxCompany->rfc;
                        $destiny['nombreRFC'] = $invoice->CustomerTaxCompany->razon_social;
                    }

                    $destiny_locations[] = $destiny;
                }
                $cartaPorte['totalDistRec'] = $totalDistRec; // En kilometros

                $locations[] = $origin;
                $locations = array_merge($locations, $destiny_locations);

                $cartaPorte['ubicaciones'] = $locations;

                $mercancias = [];
                $mercancias['pesoBrutoTotal'] = 0;      // Se saca de la suma de los pesos de mercancias
                $mercancias['unidadPeso'] = 'KGM';      // FIXME - Clave unidad viene del sat. POr mientras hardcodead
                $mercancias['numTotalMercancias'] = -1; // Contar detalles ventas o detalles movimientos
                $mercancias['mercancias'] = [];
                $mercancias['materialPeligroso'] = 'No';

                $arrMercancias = [];

                if ($isSale) {
                    if ($invoice) {
                        // Realizamos la peticion de acuerdo al tipo de producto de venta
                        $cpt = [];
                        $bales_detail = $invoice->InvoiceDetails;
                        $inBulk_detail = $invoice->InvoiceInBulkDetails;
                        $laminate_detail = $invoice->InvoiceLaminateDetails;

                        $cartaPorte['HayMatPel'] = false;

                        $mercancias['numTotalMercancias'] = ($bales_detail->count() ?? 0) + ($inBulk_detail->count() ?? 0) + ($laminate_detail->count() ?? 0);

                        if ($bales_detail) {
                            foreach ($bales_detail as $detail) {
                                $mercancia = [
                                    'bienesTransp' => $detail->Bales->Products->clave_producto_id,
                                    'descripcion' =>  $detail->Bales->Products->name,
                                    'cantidad' => $detail->Bales->qty,
                                    'claveUnidad' => 'KGM',
                                    'unidad' => 'KILOGRAMOS',
                                    'dimensiones' => '',
                                    'materialPeligroso' => 'No', // '0',
                                ];

                                $weight = $detail->qty * $detail->Bales->Products->weight; // Aqui se convierte la cantidad a KG

                                $mercancia['pesoEnKg'] = $weight;
                                $mercancias['pesoBrutoTotal'] += floatval($mercancia['pesoEnKg']);
                                $mercancia['valorMercancia'] = 0;
                                $mercancia['moneda'] = 'MXN';
                                $mercancia['cantidadTransporta'] = [
                                    'cantidad' => $detail->qty,
                                    'iDOrigen' => $origin['iDUbicacion'],
                                    'iDDestino' => $destiny['iDUbicacion']
                                ];

                                $mercancias['mercancias'][] = $mercancia;
                            }
                        }
                        if ($inBulk_detail) {
                            foreach ($inBulk_detail as $detail) {
                                $mercancia = [
                                    'bienesTransp' =>$detail->Products->clave_producto_id,
                                    'descripcion' =>  $detail->Products->name,
                                    'cantidad' => $detail->qty,
                                    'claveUnidad' => 'KGM',
                                    'unidad' => 'KILOGRAMOS',
                                    'dimensiones' => '',
                                    'materialPeligroso' => 'No', // '0',
                                ];

                                $weight = $detail->qty * 1; // Aqui se convierte la cantidad a KG

                                $mercancia['pesoEnKg'] = $weight;
                                $mercancias['pesoBrutoTotal'] += floatval($mercancia['pesoEnKg']);
                                $mercancia['valorMercancia'] = 0;
                                $mercancia['moneda'] = 'MXN';
                                $mercancia['cantidadTransporta'] = [
                                    'cantidad' => $detail->qty,
                                    'iDOrigen' => $origin['iDUbicacion'],
                                    'iDDestino' => $destiny['iDUbicacion']
                                ];

                                $mercancias['mercancias'][] = $mercancia;
                            }
                        }
                        if ($laminate_detail) {
                            foreach ($laminate_detail as $detail) {
                                $mercancia = [
                                    'bienesTransp' => $detail->WmLaminates->Products->clave_producto_id ?? $detail->Products->clave_producto_id,
                                    'descripcion' => $detail->WmLaminates->Products->name ?? $detail->Products->name,
                                    'cantidad' => $detail->qty,
                                    'claveUnidad' => 'KGM',
                                    'unidad' => 'KILOGRAMOS',
                                    'dimensiones' => '',
                                    'materialPeligroso' => 'No', // '0',
                                ];

                                $weight = $detail->qty * 1; // Aqui se convierte la cantidad a KG

                                $mercancia['pesoEnKg'] = $weight;
                                $mercancias['pesoBrutoTotal'] += floatval($mercancia['pesoEnKg']);
                                $mercancia['valorMercancia'] = 0;
                                $mercancia['moneda'] = 'MXN';
                                $mercancia['cantidadTransporta'] = [
                                    'cantidad' => $detail->qty,
                                    'iDOrigen' => $origin['iDUbicacion'],
                                    'iDDestino' => $destiny['iDUbicacion']
                                ];

                                $mercancias['mercancias'][] = $mercancia;
                            }
                        }
                    }
                }

                /* Campo permSCT:
                              TPAF01 Autotransporte Federal de carga general.
                              TPAF02 Transporte privado de carga.
                              TPAF03 Autotransporte Federal de Carga Especializada de materiales y residuos peligrosos.
                */

                $autoTransporte = [
                    'permSCT' => $vehicle->perm_sct,
                    'numPermisoSCT' => $vehicle->perm_num_sct,
                    'identificacionVehicular' => [
                        'configVehicular' => $vehicle->Autotransport->code,
                        'placaVM' => $vehicle->license_plate,
                        'anioModeloVM' => $vehicle->year,
                    ],
                    'seguros' => [
                        'aseguraRespCivil' => $vehicle->insurance_civil_resp,
                        'polizaRespCivil' => $vehicle->resp_civil_policy,
                        'aseguraMedAmbiente' => $vehicle->ambience_insurance,
                        'polizaMedAmbiente' => $vehicle->ambience_insurance_policy,
                    ]
                ];
                $autoTransporte['es_remolque'] = $vehicle->has_towing;

                if ($vehicle->has_towing) {
                    $autoTransporte['remolques'] = [];
                    $autoTransporte['remolques']['subTipoRem'] = $vehicle->Towing->code;
                    $autoTransporte['remolques']['placa'] = $vehicle->towing_plate;
                }

                $mercancias['autoTransporte'] = $autoTransporte;
                $cartaPorte['mercancias'] = $mercancias;

                $figuraTransporte = [];
                foreach ($porterage->Drivers as $tripDriver) {
                    $tiposFigura = [
                        'tipoFigura' => '01',
                        'rfcFigura' => trim($tripDriver->rfc),
                        'numLicencia' => trim($tripDriver->license),
                        'nombreFigura' => trim($tripDriver->name),
                    ];
                    $figuraTransporte[] = $tiposFigura;
                }

                // $figuraTransporte[] = $tiposFigura;
                $cartaPorte['figuraTransporte'] = $figuraTransporte;
                $data['cartaPorte'] = $cartaPorte;

                $emision = $this->createEmisionConsigmentNote($data);
                // var_dump($emision);exit;
                $this->content['emision'] = $emision;
                // $emision = $this->prueba2();
                // $name = "TIMBRAR_CartaPorte_{$invoice->id}.xml"; // eg: emisionCoreyAl_ + TIMBRAR_CP_1.xml\
                $name = $porterage->folio . '_rebasa-P.xml';
                $resp = $this->sendXMLEmision($emision, $name);
                if ($resp->result) {
                    $porterage->status_timbrado = 4;
                    $porterage->id_request = $resp->uuid;
                    $porterage->message = $resp->message;
                    if ($porterage->update()) {
                        $this->content['result'] = 'success';
                        $this->content['status'] = 4;
                        $this->content['data'] = $data;
                        $this->content['resp'] = $resp;
                    }
                } else {
                    $porterage->message = $data->message;
                    if ($porterage->update()) {
                        $this->content['result'] = 'success';
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function createEmisionConsigmentNote(array $data): string
    {
        $conceptos = $data['conceptos'];
        $cartaPorte = $data['cartaPorte']; // object
        $HayMatPel = $cartaPorte['HayMatPel'];
        $ubicaciones = $cartaPorte['ubicaciones']; // array
        $mercancias = $cartaPorte['mercancias']; // object
        $autoTransporte = $mercancias['autoTransporte']; // object
        $identificacionVehicular = $autoTransporte['identificacionVehicular']; // object
        $seguros = $autoTransporte['seguros']; // object
        $remolques = $autoTransporte['remolques'] ?? []; // object
        $figuraTransporte = $cartaPorte['figuraTransporte']; // array

        $emision = "
			<emision>
				<cliente>$data[company_id]</cliente>
				<factura>
					<data>
						<folio>$data[folio]</folio>
						<fecha>$data[expedition_date]</fecha>
						<subtotal>0</subtotal>
						<total>0</total>
						<tipoDeComprobante>T</tipoDeComprobante>
						<lugarDeExpedicion>$data[expedition_place]</lugarDeExpedicion>
						<moneda>XXX</moneda>
						<condPago></condPago>
					</data>
					<receptor>
						<rfc>$data[rfc]</rfc>
						<nombre>$data[name]</nombre>
						<usoCFDI>P01</usoCFDI>
					</receptor>
					<conceptos>";
        foreach ($conceptos as $concepto) {
            $emision .= "
						<concepto>
							<cantidad>$concepto[cantidad]</cantidad>
							<claveUnidad>$concepto[claveUnidad]</claveUnidad>
							<claveProdServ>$concepto[claveProdServ]</claveProdServ>
							<descripcion>$concepto[descripcion]</descripcion>
							<valorUnitario>$concepto[valorUnitario]</valorUnitario>
							<importe>$concepto[importe]</importe>
						</concepto>";
        }
        $emision .= "
					</conceptos>
					<complemento>
						<cartaPorte>
							<transpInternac>$cartaPorte[transpInternac]</transpInternac>
							<totalDistRec>$cartaPorte[totalDistRec]</totalDistRec>
							<ubicaciones>";
        foreach ($ubicaciones as $ubicacion) {
            $domicilio = $ubicacion['domicilio'];
            $emision .= "		<ubicacion>
									<tipoUbicacion>$ubicacion[tipoUbicacion]</tipoUbicacion>
							        <iDUbicacion>$ubicacion[iDUbicacion]</iDUbicacion>
							        <rfcRemitenteDestinatario>$ubicacion[rfcRemitenteDestinatario]</rfcRemitenteDestinatario>
							        
							        <fechaHoraSalidaLlegada>$ubicacion[fechaHoraSalidaLlegada]</fechaHoraSalidaLlegada>";
            $emision .= ($ubicacion['distanciaRecorrida'] > 0) ?
                "<distanciaRecorrida>$ubicacion[distanciaRecorrida]</distanciaRecorrida>" : "";
            $emision .= "			<domicilio>
								        <calle>$domicilio[calle]</calle>
								        <numeroExterior>$domicilio[numeroExterior]</numeroExterior>
                                        ".($domicilio['numeroInterior']==''?'':"<numeroInterior>$domicilio[numeroInterior]</numeroInterior>")."
								        
								        <colonia>$domicilio[colonia]</colonia>
								        ".($domicilio['localidad']!=null?"<localidad>$domicilio[localidad]</localidad>":'')."
								        
								        <municipio>$domicilio[municipio]</municipio>
								        <estado>$domicilio[estado]</estado>
								        <pais>$domicilio[pais]</pais>
								        <codigoPostal>$domicilio[codigoPostal]</codigoPostal>
							        </domicilio>
							    </ubicacion>";
        }
        $emision .= "		</ubicaciones>
							<mercancias>
								<pesoBrutoTotal>$mercancias[pesoBrutoTotal]</pesoBrutoTotal>
								<unidadPeso>$mercancias[unidadPeso]</unidadPeso>
								<numTotalMercancias>$mercancias[numTotalMercancias]</numTotalMercancias>";
        foreach ($mercancias['mercancias'] as $mercancia) {
            $cantidadTransporta = $mercancia['cantidadTransporta'];
            $emision .= "		<mercancia>
							        <bienesTransp>$mercancia[bienesTransp]</bienesTransp>
							        <descripcion>$mercancia[descripcion]</descripcion>
							        <cantidad>$mercancia[cantidad]</cantidad>
							        <claveUnidad>$mercancia[claveUnidad]</claveUnidad>
							        <unidad>$mercancia[unidad]</unidad>";
            $emision .= !empty($mercancia['dimensiones']) ? "<dimensiones>$mercancia[dimensiones]</dimensiones>" : "";

            $emision .= !empty($mercancia['cveMaterialPeligroso']) ? "
									<cveMaterialPeligroso>$mercancia[cveMaterialPeligroso]</cveMaterialPeligroso>" : '';
            $emision .= !empty($mercancia['embalaje']) ? "
									<embalaje>$mercancia[embalaje]</embalaje>" : '';
            $emision .= !empty($mercancia['descripEmbalaje']) ? "
									<descripEmbalaje>$mercancia[descripEmbalaje]</descripEmbalaje>" : '';

            $emision .= "			<pesoEnKg>$mercancia[pesoEnKg]</pesoEnKg>";
            $emision .= "			<valorMercancia>$mercancia[valorMercancia]</valorMercancia>
									<moneda>$mercancia[moneda]</moneda>
									<cantidadTransporta>
										<cantidad>$cantidadTransporta[cantidad]</cantidad>
										<iDOrigen>$cantidadTransporta[iDOrigen]</iDOrigen>
										<iDDestino>$cantidadTransporta[iDDestino]</iDDestino>
									</cantidadTransporta>
								</mercancia>";
        }
        $emision .= "			<autoTransporte>
									<permSCT>$autoTransporte[permSCT]</permSCT>
									<numPermisoSCT>$autoTransporte[numPermisoSCT]</numPermisoSCT>
									<identificacionVehicular>
										<configVehicular>$identificacionVehicular[configVehicular]</configVehicular>
										<placaVM>$identificacionVehicular[placaVM]</placaVM>
										<anioModeloVM>$identificacionVehicular[anioModeloVM]</anioModeloVM>
									</identificacionVehicular>
									<seguros>
										<aseguraRespCivil>$seguros[aseguraRespCivil]</aseguraRespCivil>
										<polizaRespCivil>$seguros[polizaRespCivil]</polizaRespCivil>";

        if ($HayMatPel) {
            $emision .= "				<aseguraMedAmbiente>$seguros[aseguraMedAmbiente]</aseguraMedAmbiente>
										<polizaMedAmbiente>$seguros[polizaMedAmbiente]</polizaMedAmbiente>";
        }
        $emision .= "</seguros>";

        if ($autoTransporte['es_remolque']) {
            $emision .= "			<remolques>
										<remolque>
											<subTipoRem>$remolques[subTipoRem]</subTipoRem>
											<placa>$remolques[placa]</placa>
										</remolque>
									</remolques>";
        }
        $emision .= "			</autoTransporte>
							</mercancias>
							<figuraTransporte>";
        if (count($figuraTransporte) > 0) {
            foreach ($figuraTransporte as $tipoFigura) {
                $emision .= "	<tiposFigura>
									<tipoFigura>$tipoFigura[tipoFigura]</tipoFigura>
									<rfcFigura>$tipoFigura[rfcFigura]</rfcFigura>
									<numLicencia>$tipoFigura[numLicencia]</numLicencia>
									<nombreFigura>$tipoFigura[nombreFigura]</nombreFigura>
								</tiposFigura>";
            }
        }
        $emision .= "		</figuraTransporte>
						</cartaPorte>
					</complemento>
				</factura>
			</emision>";
        return $emision;
    }

    public function sendXMLEmision(string $dataEmision, string $name)
    {
        $serviceURL = $this->batuta_url . '/api/get_emision';
        $curlPostData = [
            'data' => $dataEmision,
            'filename' => $name,
            'flavour' => 'Facturacion',
            'scheduled' => ''
        ];
        $data = $this->sendEmision($curlPostData, $serviceURL);
        return $data;
    }

    private function sendEmision($curlPostData, $serviceURL)
    {
        $curl = curl_init($serviceURL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPostData);
        $curlResponse = curl_exec($curl);

        $data = [];
        if ($curlResponse === false) {
            $data['info'] = curl_getinfo($curl);
            $data['result'] = false;
        } else {
            $data = json_decode($curlResponse);
        }
        curl_close($curl);
        return (object) $data;
    }

    public function checkInvoice($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $porterage = Trips::findFirst($id);
                if ($porterage && $porterage->status_timbrado == 4) {
                    $porterage->setTransaction($tx);
                    $service_url = $this->batuta_url . '/api/info_factura';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'uuid' => $porterage->id_request,
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response === false) {
                        $info = curl_getinfo($curl);
                        curl_close($curl);
                        die('error occured during curl exec. Additioanl info: ' . var_export($info));
                    } else {
                        $response = json_decode($curl_response);
                        if ($response->status == 'done') {
                            $service_url = $this->batuta_url . '/api/get_uuid';
                            $curl = curl_init($service_url);
                            $curl_post_data = array(
                                'uuid' => $porterage->id_request,
                            );
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                            $curl_response2 = curl_exec($curl);
                            if ($curl_response2 === false) {
                                $info = curl_getinfo($curl);
                                curl_close($curl);
                                die('error occured during curl exec. Additioanl info: ' . var_export($info));
                            } else {
                                $uuid_factura = $curl_response2;
                                $porterage->status_timbrado = 1;
                                $porterage->uuid = $uuid_factura;
                                $porterage->message = $response->message;
                                if ($porterage->update()) {
                                    $this->content['result'] = 'success';
                                } else {
                                    $this->content['error'] = Helpers::getErrors($porterage);
                                    $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                    $tx->rollback();
                                }
                            }
                        } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                            $porterage->message = $response->message;
                            if ($porterage->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($porterage);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        } else  if ($response->status == 'Error' || $response->status == 'error') {
                            $porterage->status_timbrado = 6;
                            $porterage->message = $response->message;
                            if ($porterage->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($porterage);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        }
                    }
                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                } else if ($porterage && $porterage->status_timbrado == 3) {
                    $porterage->setTransaction($tx);
                    $service_url = $this->batuta_url . '/api/info_general';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'uuid' => $porterage->id_cancelacion,
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response === false) {
                        $info = curl_getinfo($curl);
                        curl_close($curl);
                        die('error occured during curl exec. Additioanl info: ' . var_export($info));
                    } else {
                        $response = json_decode($curl_response);
                        if ($response->status == 'done') {
                            $porterage->status_timbrado = 5;
                            $porterage->id_cancelacion_asc = $response->message;
                            if ($porterage->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($porterage);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        } else if ($response->status == 'incoming' || $response->status == 'in progress' || $response->status == 'new') {
                            $porterage->message_cancelacion = $response->message;
                            if ($porterage->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($porterage);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        } else  if ($response->status == 'Error' || $response->status == 'error') {
                            $porterage->status_timbrado = 1;
                            $porterage->message_cancelacion = $response->message;
                            if ($porterage->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($porterage);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        }
                    }
                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                } else if ($porterage && $porterage->status_timbrado == 5) {
                    $service_url = $this->batuta_url . '/api/get_status_cancelacion';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'uuid' => $porterage->id_cancelacion_asc,
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response === false) {
                        $info = curl_getinfo($curl);
                        curl_close($curl);
                        die('error occured during curl exec. Additioanl info: ' . var_export($info));
                    } else {
                        $response = json_decode($curl_response);
                        if ($response->ret_status == 200) {
                            if ($response->status) {
                                $porterage->status_timbrado = 2;
                                $porterage->acusesat_cancelacion = $response->acuseSat;
                                $porterage->fecha_cancelacion_recibido = date('Y-m-d H:i:s');
                                if ($porterage->update()) {
                                    $this->content['result'] = 'success';
                                } else {
                                    $this->content['error'] = Helpers::getErrors($porterage);
                                    $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                    $tx->rollback();
                                }
                            } else {
                                $status = explode('|', str_replace(' ', '', $response->message))[0];
                                if ($status == 211) {
                                //    $porterage->message_cancelacion = $response->message;
                                    if ($porterage->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($porterage);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                } else {
                                    $porterage->status_timbrado = 7;
                                //    $porterage->message_cancelacion = $response->message;
                                //    $porterage->acuseSat_cancelacion = $response->acuseSat;
                                    if ($porterage->update()) {
                                        $this->content['result'] = 'success';
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($porterage);
                                        $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                        $tx->rollback();
                                    }
                                }
                            }
                        } else if ($response->ret_status == 211) {
                            // $porterage->message_cancelacion = $response->message;
                            if ($porterage->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($porterage);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        } else {
                            $porterage->status_timbrado = 7;
                        //    $porterage->message_cancelacion = $response->message;
                        //    $porterage->acuseSat_cancelacion = $response->acuseSat;
                            if ($porterage->update()) {
                                $this->content['result'] = 'success';
                            } else {
                                $this->content['error'] = Helpers::getErrors($porterage);
                                $this->content['message'] = ['title' => '¡Error!', 'content' => $this->content['error'][1]];
                                $tx->rollback();
                            }
                        }
                    }
                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                }
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
            }
            $this->content['response'] = $response;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function cancelar($id)
    {
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();
            $request = $this->request->getPut();
            try {
                $invoice = Trips::findFirst($id);
                $factura = Invoices::findFirst($invoice->invoice_id);
                $clienteBatuta = 80;
                $invoice->motivo_cancelacion = $request['motivo_cancelacion'];
                $invoice->folio_sustituye = $request['folio_sustituye']!=""?$request['folio_sustituye']:null;
                switch ($factura->ShoppingCart->branchoffice) {
                    case 9:
                        $clienteBatuta = 80;
                        break;
                    case 13:
                        $clienteBatuta = 82;
                        break;
                    case 12:
                        $clienteBatuta = 81;
                        break;
                    case 14:
                        $clienteBatuta = 82;
                        break;
                    default:
                        $clienteBatuta = 80;
                        break;
                }
                if ($invoice) {
                    if($invoice->status_timbrado != 1 && $invoice->status_timbrado != 7){
                        $this->content['result'] == 'success';
                        return;
                    }
                    $folio_sustituye = '';
                    if($invoice->folio_sustituye != null && $invoice->motivo_cancelacion == '01'){
                        $invoiceSustituye = Trips::findFirst("
                        folio = $invoice->folio_sustituye 
                        and status_timbrado = 1
                        and folio != $invoice->folio");
                        if(!$invoiceSustituye || $invoice->Invoices->tax_company_id != $invoiceSustituye->Invoices->tax_company_id){
                            $this->content['errors'] = "Verifiacar que el folio corresponda a una factura del mismo cliente y no este cancelada.";
                            $this->content['message'] = Message::error('Folio que sustituye no es valido.');
                            $this->content['result'] = 'error';
                            $response = ['error' => 'Factura con pagos relacionados.'];
                            $this->response->setJsonContent($this->content);
                            $this->response->send();
                            return;
                        }
                        $folio_sustituye = "|$invoiceSustituye->uuid";
                    }
                    $CancelTimbrado = "<cancelarTimbrado>
                        <client>".$clienteBatuta."</client>
                        <uuid>{$invoice->uuid}|{$invoice->motivo_cancelacion}{$folio_sustituye}</uuid>
                        <type>cancelarTimbrado</type>
                        </cancelarTimbrado>";

                    $service_url = $this->batuta_url.'/api/get_emision';
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'data' => $CancelTimbrado,
                        'filename' => 'ctimbrado_' . $invoice->uuid . '.xml',
                        'flavour' => 'CancelarFacturaNew',
                        'scheduled' => '',
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response !== false) {
                        $response = json_decode($curl_response);
                        $invoice->setTransaction($tx);
                        if ($invoice) {
                            if ($response->result) {
                                $invoice->status_timbrado = 3;
                                $invoice->id_cancelacion = $response->uuid;
                                $invoice->message_cancelacion = $response->message;
                                $invoice->fecha_cancelacion_envio = date('Y-m-d H:i:s');
                                foreach($pagosFolio as $pagoFolio){
                                    $pagoFolio->setTransaction($tx);
                                    $pagoFolio->status_timbrado = 3;
                                    $pagoFolio->id_cancelacion = $response->uuid;
                                    $pagoFolio->message_cancelacion = $response->message;
                                    $pagoFolio->fecha_cancelacion_envio = date('Y-m-d H:i:s');
                                    $pagoFolio->motivo_cancelacion = $invoice->motivo_cancelacion;
                                    $pagoFolio->update();
                                }
                                $invoice->update();
                                $this->content['result'] = 'success';
                            } else {
                                $invoice->message_cancelacion = $response->message;
                                $invoice->update();
                            }
                        }
                    }

                    if ($this->content['result'] == 'success') {
                        $tx->commit();
                    }
                }
            } catch (Throwable $e) {
                $this->content['errors'] = get_class($e) . ": {$e->getMessage()} ({$e->getCode()})" . PHP_EOL;
                $this->content['message'] = ['title' => '¡Error!', 'content' => $e->getMessage()];
            }
            $this->content['response'] = $response;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 7 OR role_id = 8)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
}
