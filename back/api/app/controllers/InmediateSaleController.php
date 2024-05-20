<?php

use Phalcon\Mvc\Controller;

class InmediateSaleController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function responde () {
        var_dump('lalal');
    }

    public function request ($id = null)
    {
        try {
            if ($this->userHasPermission()) {
                if (!is_null($id) && is_numeric($id)) {
                    $shoppingCart = ShoppingCart::findFirst($id);
                    if ($shoppingCart && $shoppingCart->status == 'NUEVO') {
                        $shoppingCartBaleDetails = ShoppingCartBaleDetails::find("shopping_cart_id = $shoppingCart->id");
                        $shoppingCartInBulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id");
                        $shoppingCartLaminateDetails = ShoppingCartLaminateDetails::find("shopping_cart_id = $shoppingCart->id");
                        if (count($shoppingCartBaleDetails) > 0 || count($shoppingCartInBulkDetails) > 0 || count($shoppingCartLaminateDetails) > 0) {
                            $tx = $this->transactions->get();
                            $shoppingCart->setTransaction($tx);
                            $request = $this->request->getPut();
                            // echo("<pre>");
                            // print_r($request);
                            // exit();
                            $customer = Customers::findFirst($shoppingCart->customer_id);
                            if ($customer->term == 'CREDITO') {
                                if (floatval($request['total_cost']) > floatval($customer->credit_limit)) {
                                    $shoppingCart->status = 'COTIZADO';
                                    $this->applyDiscount($id, $request['discount']);
                                    $this->content['limit_message'] = Message::error('El pedido #'.$shoppingCart->id.' del cliente '.$customer->name.' supera el límite de crédito');
                                    $this->content['message'] = Message::success('Pedido En Estatus Cotizado.');
                                } else {
                                    $restante = $this->verifyCreditLimitForCustomer($shoppingCart->customer_id);
                                    if (count($restante) > 0 && $restante != null){
                                        $totalcost = $restante[0]['cantidad_restante'] + floatval($request['total_cost']);
                                        $fechaNormal= date("Y-m-d");
                                        if ($restante[0]['term'] == 'CREDITO'){
                                            // Se validan dos cosas
                                            if ($totalcost > $restante[0]['credit_limit']) {
                                                // Se autoriza automaticamente
                                                $shoppingCart->status = 'COTIZADO';
                                                $this->applyDiscount($id,$request['discount']);
                                                $this->content['limit_message'] = Message::error('El pedido #'.$shoppingCart->id.' del cliente '.$customer->name.' supera el límite de crédito');
                                                $this->content['message'] = Message::success('Pedido En Estatus Cotizado.');
                                            } else if (!$restante[0]['canbuy']) {
                                                $shoppingCart->status = 'COTIZADO';
                                                $this->applyDiscount($id,$request['discount']);
                                                $this->content['expired_message'] = Message::error('Tiene Facturas Pendientes De Pagar');
                                                $this->content['message'] = Message::success('Pedido En Estatus Cotizado.');
                                            } else {
                                                $shoppingCart->status = 'AUTORIZADO';
                                                $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'COTIZADO'");
                                                if ($bulkDetails) {
                                                    foreach ($bulkDetails as $detail) {
                                                        $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                                                        $tx = $this->transactions->get();
                                                        $bD = ShoppingCartInBulkDetails::findfirst("id = $idb AND status = 'COTIZADO'");
                                                        $bD->setTransaction($tx);
                                                        $bD->status = 'AUTORIZADO';
                                                        // Change the price with a discount applied
                                                        $changePrice = floatval($bd->price_product) - (floatval($bd->price_product) * intval($request['discount'])) / 100;
                                                        $bD->price_product = $changePrice;
                                                        if ($bD->update()) {}
                                                    }
                                                }
                                                $this->content['message'] = Message::success('El pedido ha sido autorizado exitosamente.');
                                            }
                                        }
                                    } else {
                                        $shoppingCart->status = 'AUTORIZADO';
                                        $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'COTIZADO'");
                                        if ($bulkDetails) {
                                            foreach ($bulkDetails as $detail) {
                                                $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                                                $tx = $this->transactions->get();
                                                $bD = ShoppingCartInBulkDetails::findfirst("id = $idb AND status = 'COTIZADO'");
                                                $bD->setTransaction($tx);
                                                $bD->status = 'AUTORIZADO';
                                                $changePrice = floatval($bd->price_product) - (floatval($bd->price_product) * intval($request['discount'])) / 100;
                                                $bD->price_product = $changePrice;                                                if ($bD->update()) {}
                                            }
                                        }
                                        $this->content['message'] = Message::success('El pedido ha sido autorizado exitosamente.');
                                    }
                                }
                            } else {
                                $shoppingCart->status = 'COTIZADO';
                                $this->applyDiscount($id, $request['discount']);
                                $this->content['message'] = Message::success('El pedido ha sido Cotizado exitosamente.');
                            }
                            $shoppingCart->payment_method = $request['payment_method'] ? $request['payment_method'] : null;
                            $shoppingCart->payment_date = $request['payment_date'] ? $request['payment_date'] : null;
                            $shoppingCart->payment_reference = $request['payment_reference'] ? $request['payment_reference'] : null;
                            // $shoppingCart->oc_date = $request['oc_date'] ? $request['oc_date'] : null;
                            $shoppingCart->oc_reference = $request['oc_reference'] ? $request['oc_reference'] : null;
                            $shoppingCart->oc_term = $request['oc_term'] ? $request['oc_term'] : null;
                            $shoppingCart->applied_discount = $request['discount'] ? intval($request['discount']) : 0;
                            if ($shoppingCart->update()) {
                                $this->content['result'] = true;
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($shoppingCart);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar Cotizar el pedido.');
                            }
                        } else {
                            $this->content['message'] = Message::error('El pedido no cuenta con detalles registrados.');
                        }
                    }
                }
            }

        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function approve ($id)
    {
        try {
            $tx = $this->transactions->get();
            $shoppingCart = ShoppingCart::findFirst($id);
            $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'SOLICITADO'");
            if ($shoppingCart && $shoppingCart->status == 'COTIZADO') {
                $shoppingCart->setTransaction($tx);
                $shoppingCart->status = 'AUTORIZADO';
                if ($shoppingCart->update()) {
                    if($bulkDetails){
                        foreach ($bulkDetails as $detail) {
                            $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                            $tx = $this->transactions->get();
                            $bD = ShoppingCartInBulkDetails::findfirst("id = $idb AND status = 'SOLICITADO'");
                            $bD->setTransaction($tx);
                            $bD->status = 'AUTORIZADO';
                            if ($bD->update()) {}
                        }
                    }
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El pedido ha sido autorizado exitosamente.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($shoppingCart);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar autorizar el pedido.');
                }
            } else {
                $this->content['message'] = Message::error('No se ha encontrado el pedido.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function applyDiscount ($id, $discount) {
        $bulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'SOLICITADO'");
        if ($bulkDetails) {
            foreach ($bulkDetails as $detail) {
                $idb = $detail->id ? $idb = intval($detail->id) : $idb = '';
                $tx = $this->transactions->get();
                $bD = ShoppingCartInBulkDetails::findfirst("id = $idb AND status = 'SOLICITADO'");
                $bD->setTransaction($tx);
                $changePrice = floatval($bD->price_product) - (floatval($bD->price_product) * intval($discount)) / 100;
                $bD->price_product = $changePrice;
                if ($bD->update()) {}
            }
        }
    }

    public function getFilterExistences ($storageId, $productId) {
        
        $content = $this->content;
        $mistock = [];
        if ($this->userHasPermission()) {
            //var_dump($branchOfficeId);
            $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
            FROM wms_movement_details AS md
            JOIN wms_movements AS m ON m.movement_id = md.movement_id
            JOIN wms_storages AS s ON s.id = m.storage_id 
            JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
            JOIN wms_products AS p ON p.id = md.product_id
            JOIN wms_lines AS l ON l.id = p.line_id
            JOIN wms_categories AS c ON c.id = l.category_id
            JOIN sys_users AS u ON u.id = m.created_by
            WHERE m.status = 'EJECUTADO' ";
            $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";

            if (!is_null($storageId) && is_numeric($storageId)) {
                $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
            }
            if (!is_null($productId)) {
                $sql .= " AND md.product_id in ($productId)";
            }

            $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
                    FROM wms_movement_details AS md
                    JOIN wms_movements AS m ON m.id = md.movement_id
                    JOIN wms_storages AS s ON s.id = m.storage_id 
                    JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
                    JOIN wms_products AS p ON p.id = md.product_id
                    JOIN wms_lines AS l ON l.id = p.line_id
                    JOIN wms_categories AS c ON c.id = l.category_id
                    JOIN sys_users AS u ON u.id = m.created_by
                    WHERE m.status = 'EJECUTADO'";
                    if (!is_null($storageId) && is_numeric($storageId)) {
                        $sql .= " AND m.storage_id = $storageId";
                    }
                    
                        $sql .= " AND md.product_id in ($productId)";
                    

            $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
            
            $data = $this->db->query($sql)->fetchAll();

            $movements = $data;
            $products = [];
        $stock = [];
        foreach ($movements as $movement) {
            if (!in_array($movement['product_id'], $products)) {
                $productStock = 0;
                foreach ($movements as $secondMovement) {
                    if ($movement['product_id'] == $secondMovement['product_id']) {
                        if ($secondMovement['movement_type'] == 1) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 2) {
                            $productStock -= $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 3){
                            $productStock = $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4){
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5){
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array('qty' => $productStock, 'product_id' => $movement['product_id']));
            }
        }
            $mistock = $stock;
            /* $content['stock'] = $stock;
            $content['result'] = true; */
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        return $mistock;
    }

    public function updateDetailsWhenPostergadoChanges ($id) {
        $ibCD = ShoppingCartInBulkDetails::find("shopping_cart_id = $id AND status = 'POSTERGADO'");
        $data = false;
        $datainBulk = $ibCD->toArray();
        if(isset($datainBulk)) {
            foreach ($datainBulk as $d) {
                $tx = $this->transactions->get();
                $inBulkCartDetails = ShoppingCartInBulkDetails::findFirst($d['id']);
                $inBulkCartDetails->setTransaction($tx);
                $inBulkCartDetails->status = 'AUTORIZADO';
                if ($inBulkCartDetails->update()) {
                    $data = true;
                }
            }
        }
        return $data;
    }

    public function generateInvoice ($id)
    {
        set_time_limit(0);
        if ($this->userHasPermission() && !is_null($id) && is_numeric($id)) {
            try {
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $storage = [];
                if (isset($request['saleDate']) && isset($request['customerBranchOfficeId']) && is_numeric($request['customerBranchOfficeId'])) {
                    $shoppingCart = ShoppingCart::findFirst($id);
                    if ($shoppingCart) {
                        $shoppingCartInBulkDetails = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id AND status = 'AUTORIZADO' order by id asc");
                        //AQUI CONSULTA EL ALMACEN
                        $storage = $this->getStoragesbyShoppingCart($id);
                        //print_r($storage);
                        //exit();
                        if (count($shoppingCartInBulkDetails) > 0) {
                            $tx = $this->transactions->get();
                            $someError = false;
                            if (count($shoppingCartInBulkDetails) > 0) {
                                // Este foreach crea un movimiento nueo de salida por cada producto del pedido
                                //for ($i=0; $i < count($shoppingCartInBulkDetails); $i++) {
                                    $inBulkMovement = new Movements();
                                    $inBulkMovement->folio = 0;
                                    $inBulkMovement->storage_id = $storage;
                                    $inBulkMovement->type_id = 2;
                                    $inBulkMovement->status = 'NUEVO';
                                    $customerBranchOffice = CustomerBranchOffices::findFirst($request['customerBranchOfficeId']);
                                    if ($inBulkMovement->create()) {
                                        // $someError = true;
                                        if ($customerBranchOffice) {
                                            $customer = Customers::findFirst("id = $customerBranchOffice->customer_id AND active");
                                            if ($customer) {
                                                // Debo crear una remision por cada producto del pedido
                                                // foreach ($shoppingCartInBulkDetails as $value) {
                                                    $saleDate = date("Y-m-d", strtotime($request['saleDate']));
                                                    $invoice = new Invoices();
                                                    $invoice->setTransaction($tx);
                                                    $invoice->sale_date = $saleDate;
                                                    $invoice->agent_id = $validUser->id;
                                                    // $invoice->branch_office_id = 1;
                                                    $invoice->customer_branch_office_id = $request['customerBranchOfficeId'];
                                                    //$invoice->driver_id = $request['driverId']?$request['driverId']: null;
                                                    $invoice->shopping_cart_id = $shoppingCart->id;
                                                    $invoice->seller_id = $shoppingCart->user_id; // Id del usuario que creo la venta
                                                    $invoice->in_bulk_movement_id = $inBulkMovement->id;
                                                    if (isset($request['comments']) && $request['comments'] && strlen($request['comments']) > 0) {
                                                        $invoice->comments = strtoupper($request['comments']);
                                                    }
                                                    if ($invoice->create()) {
                                                        
                                                        $correctDetails = true;
                                                        if ($shoppingCart->update()) {
                                                            $inBulkDetails = [];
                                                            $parciales = [];
                                                            $totales = [];
                                                            // In bulk details
                                                            if (count($shoppingCartInBulkDetails) > 0) {
                                                                 $inBulkProducts =[];
                                                            if($shoppingCartInBulkDetails){
                                                                foreach ($shoppingCartInBulkDetails as $detail){
                                                                    var_dump("Este foreach");
                                                                    $aux = $movements->generateStorageInventoryv3(null,$storage,null,null,$detail->product_id,null,null,null);
                                                                    array_push($inBulkProducts, $aux['data'][0]);
                                                                    }
                                                                 }
                                                                if($inBulkProducts) {
                                                                    
                                                                    for ($i=0; $i < count($shoppingCartInBulkDetails); $i++) {
                                                                    for ($j=0; $j < count($inBulkProducts); $j++) {
                                                                        if ($inBulkProducts[$j]['product_id'] == $shoppingCartInBulkDetails[$i]->product_id && $inBulkProducts[$j]['stock'] >= $shoppingCartInBulkDetails[$i]->qty) {
                                                                            $inBulkProducts[$j]['stock'] -= $shoppingCartInBulkDetails[$i]->qty;
                                                                            $shoppingCartInBulkDetail = (array) $shoppingCartInBulkDetails[$i];
                                                                            $shoppingCartInBulkDetail['stock'] = true;
                                                                            array_push($inBulkDetails, $shoppingCartInBulkDetail);
                                                                        }
                                                                    }
                                                                    }

                                                                    foreach ($inBulkDetails as $detail) {
                                                                        $invoiceDetail = new InvoiceInBulkDetails();
                                                                        $invoiceDetail->setTransaction($tx);
                                                                        $invoiceDetail->invoice_id = $invoice->id;
                                                                        $invoiceDetail->product_id = $detail['product_id'];
                                                                        $invoiceDetail->ieps = $detail['ieps'];
                                                                        $invoiceDetail->qty = $detail['qty'];
                                                                        if ($detail['price_product'] && is_numeric($detail['price_product'])) {
                                                                            $invoiceDetail->unit_price = $detail['price_product'];
                                                                        }
                                                                        else {
                                                                            $invoiceDetail->unit_price = 0;
                                                                        }
                                                                        if ($invoiceDetail->create()) {
                                                                            $cnd = false;
                                                                            // Los productos cambian a estatus REMISIONADO
                                                                            $id = intval($detail['id']);
                                                                            $shoppingCartInBulkDetail = ShoppingCartInBulkDetails::findFirst($id);
                                                                            $shoppingCartInBulkDetail->setTransaction($tx);
                                                                            $shoppingCartInBulkDetail->status = 'REMISIONADO';
                                                                            $shoppingCartInBulkDetail->invoice_id = $invoice->id;
                                                                            if (!$shoppingCartInBulkDetail->update()) {
                                                                                $cnd = true;
                                                                                $correctDetails = false;
                                                                            }
                                                                        } else {
                                                                            $correctDetails = false;
                                                                        }
                                                                        if(isset($cnd) === true){
                                                                            // ENTRA a cambiar los postergados a autorizados
                                                                            $changeinBulk = $this->updateDetailsWhenPostergadoChanges($detail['shopping_cart_id']);
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            if ((count($inBulkDetails) > 0)) {
                                                                if (count($inBulkDetails) == count($shoppingCartInBulkDetails)) {
                                                                    $shoppingCart->setTransaction($tx);
                                                                    if (isset($parcialidad)){
                                                                        if(intval($parcialidad) > 0){
                                                                            $shoppingCart->status = 'AUTORIZADO';
                                                                        } else {
                                                                            $condicion_inbulk = 0;
                                                                            $scibd = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id AND (status = 'AUTORIZADO' OR status = 'POSTERGADO') order by id asc");
                                                                            if($scibd){
                                                                                $condicion_inbulk = (count($scibd) > 0) ? count($scibd)  : 0;
                                                                            }
                                                                            if($condicion_inbulk > 0){
                                                                                $shoppingCart->status = 'AUTORIZADO';
                                                                            } else {
                                                                                $shoppingCart->status = 'REMISIONADO';
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $condicion_inbulk = 0;
                                                                        $scibd = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id AND (status = 'AUTORIZADO' OR status = 'POSTERGADO') order by id asc");
            
                                                                        if($scibd){
                                                                            $condicion_inbulk = (count($scibd) > 0) ? count($scibd)  : 0;
                                                                        }
            
                                                                        if($condicion_inbulk > 0){
                                                                            $shoppingCart->status = 'AUTORIZADO';
                                                                        } else {
                                                                            $shoppingCart->status = 'REMISIONADO';
                                                                        }
                                                                    }
                                                                    if ($shoppingCart->update()) {
                                                                        $this->content['result'] = true;
                                                                        $this->content['invoice'] = $invoice;
                                                                        $this->content['status'] = 'REMISIONADO';
                                                                        $this->content['message'] = Message::success('Venta de productos registrada correctamente con el ID '.$invoice->id.'.');
                                                                        $tx->commit();
                                                                    } else {
                                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos1.');
                                                                        $tx->rollback();
                                                                    }
                                                                }
                                                            }
                                                            // Debo de hacer la actualizacion del pedido una sola vez
                                                            else {
                                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos3.');
                                                                $tx->rollback();
                                                            }
                                                        } else {
                                                            $this->content['error'] = Helpers::getErrors($shoppingCart);
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos4.');
                                                            $tx->rollback();
                                                        }
                                                        $this->content['message'] = Message::success('Venta de productos registrada correctamente con el ID '.$invoice->id.'.');
                                                    } else {
                                                        $this->content['error'] = Helpers::getErrors($invoice);
                                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos5.');
                                                        $tx->rollback();
                                                    }


                                                // }
                                            } else {
                                                $this->content['message'] = Message::error('No se ha encontrado el cliente seleccionado.');
                                                $tx->rollback();
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('No se ha encontrado la sucursal de cliente seleccionada.');
                                            $tx->rollback();
                                        }
                                    }
                                //}
                                if ($correctDetails && (count($inBulkDetails) > 0)) {
                                    // Si es 1 quiere decir que si remiosiono productos del pedido
                                    if (count($inBulkDetails) == 1) {
                                        $shoppingCart->setTransaction($tx);
                                            $condicion_inbulk = 0;
                                            $scibd = ShoppingCartInBulkDetails::find("shopping_cart_id = $shoppingCart->id AND (status = 'AUTORIZADO' OR status = 'POSTERGADO') order by id asc");
                                            if($scibd){
                                                $condicion_inbulk = (count($scibd) > 0) ? count($scibd)  : 0;
                                            }
                                            // Que condicion_inbulk sea mayor que 0 quiere decir que hay pedidos postergados
                                            if($condicion_inbulk > 0){
                                                $shoppingCart->status = 'PARCIAL';
                                            } else {
                                                $shoppingCart->status = 'REMISIONADO';
                                            }
                                        if ($shoppingCart->update()) {
                                            $this->content['result'] = true;
                                            $this->content['invoice'] = $invoice;
                                            $this->content['status'] = 'REMISIONADO';
                                            $tx->commit();
                                        } else {
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos .');
                                            $tx->rollback();
                                        }
                                    } else {
                                        $this->content['result'] = true;
                                        $this->content['invoice'] = $invoice;
                                        $this->content['message'] = Message::success('Venta de productos registrada correctamente con el ID '.$invoice->id.'.');
                                        $tx->commit();
                                    }
                                }
                            }
                            if ($someError) {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la venta de productos 1.');
                            } else {

                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el carrito de compras.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se han recibido los parámetros necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 6 OR role_id = 7 OR role_id = 20 OR role_id = 29 OR role_id = 22  OR role_id = 27 OR role_id = 17 OR role_id = 29 OR role_id = 28)
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