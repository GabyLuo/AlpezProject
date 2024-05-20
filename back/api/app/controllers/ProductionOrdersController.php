<?php

use Phalcon\Mvc\Controller;

class ProductionOrdersController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getOrders ()
    {
        if ($this->userHasPermission()) {
            $orders = [];
            $sql = "SELECT po.id, po.order_number, TO_CHAR(po.production_date, 'dd/mm/yyyy') AS production_date, TO_CHAR(po.created, 'dd/mm/yyyy') AS created, po.qty, po.status, p.name AS product, u.name AS unit,
                    (SELECT COUNT(l.id) FROM prd_lots AS l WHERE l.order_id = po.id) AS lot_all, (SELECT COUNT(l.id) FROM prd_lots AS l WHERE l.order_id = po.id AND l.status = 'FINALIZADO') AS lot_completed
                    FROM prd_orders AS po
                    LEFT JOIN wms_products AS p
                    ON po.product_id = p.id
                    LEFT JOIN wms_units AS u
                    ON po.unit_id = u.id
                    ORDER BY po.id ASC;";
            $ordersAux = $this->db->query($sql)->fetchAll();
            $this->content['orders'] = $ordersAux;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOrder ($id)
    {
        if ($this->userHasPermission()) {
            $order = [];
            if (is_numeric($id)) {
                $sql = "SELECT po.id, po.order_number, po.production_date, po.updated, po.updated_by, po.product_id, po.qty, po.unit_id, po.status, CONCAT(p.code,' - ',p.name) as product, u.name AS unit, wc.id AS category_id, wc.name as category_name,wl.category_id AS category_aux, p.code
                        FROM prd_orders AS po
                        LEFT JOIN wms_products AS p
                        ON po.product_id = p.id
                        LEFT JOIN wms_units AS u ON po.unit_id = u.id
                        LEFT JOIN wms_lines AS wl ON wl.id = p.line_id 
                        LEFT JOIN wms_categories AS wc ON wc.id = wl.category_id
                        WHERE po.id = $id;";
                        // print_r($sql);
                        // exit();
                $order = $this->db->query($sql)->fetch();
                $orderdata = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
            }
            $this->content['order'] = $order;
            $this->content['orderdata'] = $orderdata;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getByOrder ($orden_id) {
        try {
            if ($this->userHasPermission()) {

                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                // print_r($orden_id);
                // exit();

                $sql="SELECT prd_orders.id as order_id,wms_bom.material_id,prd_orders.product_id as bom_products_id, wms_products.name as product_name,
                    wms_products.code as product_code, wms_lines.name as line_name,wms_lines.code as line_code, 
                    wms_lines.id as line_id, wms_categories.name as category_name, wms_categories.code as category_code,
                    wms_bom.amount,prd_orders.qty, sum(wms_bom.amount * prd_orders.qty) as total_necesario
                    FROM wms_bom
                    inner join wms_products on wms_products.id = wms_bom.material_id
                    inner join wms_lines on wms_lines.id = wms_products.line_id
                    inner join wms_categories on wms_categories.id = wms_lines.category_id
                    inner join prd_orders on prd_orders.product_id =wms_bom.product_id
                    where prd_orders.id = $orden_id
                    group by wms_bom.material_id,wms_products.name,wms_lines.name,wms_lines.id,
                    wms_bom.amount, prd_orders.qty,wms_products.id,wms_categories.name,wms_categories.code,
                    prd_orders.id, wms_bom.id
                    order by wms_bom.id";

                    // print_r($sql);
                    // exit();

                $detalles = $this->db->query($sql)->fetchAll();
                $aux=0;
                $cantidades_suficientes = 'si';
                $bom_by_producto = [];
                $producto = "";
                if($detalles){
                foreach ($detalles as $bom_producto) {
                    $b=(object)array();
                    $b->order_id = $bom_producto['order_id'];
                    $b->material_id = $bom_producto['material_id'];
                    $producto_existencia = $bom_producto['material_id'];
                    $b->bom_products_id = $bom_producto['bom_products_id'];
                    $b->product_name = $bom_producto['product_name'];
                    $b->product_code = $bom_producto['product_code'];
                    $b->line_name = $bom_producto['line_name'];
                    $b->line_code = $bom_producto['line_code'];
                    $b->line_id = $bom_producto['line_id'];
                    $b->category_name = $bom_producto['category_name'];
                    $almacen = $bom_producto['category_code'];
                    $b->category_code = $bom_producto['category_code'];
                    $b->amount = $bom_producto['amount'];
                    $b->qty = $bom_producto['qty'];
                    $b->total_necesario = $bom_producto['total_necesario'];

                    

                    $existencia = $this->getKardex(null, null, null, 34, $producto_existencia);
                    if ($existencia > 0) {
                        $b->existencia = $existencia;
                    } else {
                        $b->existencia = 0;
                        // $aux++;
                    }
                    if ($bom_producto['total_necesario'] > $b->existencia) {
                        $cantidades_suficientes = 'no';
                        // print_r($producto_existencia);
                        $product = Products::findFirst($producto_existencia)->name;
                        // print_r($product);
                        // exit();
                        }
                    array_push($bom_by_producto, $b);
                }

                if($cantidades_suficientes == 'si'){

                    $tx = $this->transactions->get();
                    $branchTransferX = [];
                    $transaction = new Transactions();
                    $transaction->setTransaction($tx);
                    if ($transaction->create()) {
                        $exitMovement = new Movements();
                        $exitMovement->setTransaction($tx);
                        $exitMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                        $exitMovement->date = date("Y-m-d H:i:s");
                        $exitMovement->status = 'NUEVO';
                        $exitMovement->storage_id = intval(34);
                        $exitMovement->type_id = 5;
                        $exitMovement->po_id = intval($orden_id);
                        $exitMovement->transaction_id = $transaction->id;
                    if ($exitMovement->create()) {
                            $entryMovement = new Movements();
                            $entryMovement->setTransaction($tx);
                            $entryMovement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                            $entryMovement->date = date("Y-m-d H:i:s");
                            $entryMovement->status= 'NUEVO';
                            $entryMovement->storage_id = intval(35);
                            $entryMovement->movement_id = $exitMovement->id;
                            $entryMovement->type_id = 4;
                            $entryMovement->po_id = intval($orden_id);
                            $entryMovement->transaction_id = $transaction->id;
                        if ($entryMovement->create()) {
                            $branchTransfer = new BranchTransfers();
                            $branchTransfer->setTransaction($tx);
                            $branchTransfer->transaction_id = $transaction->id;
                                if ($branchTransfer->create()) {
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('Transferencia registrada correctamente.');
                                    $this->content['branchTransfer'] = $exitMovement;

                                    foreach ($bom_by_producto as $detail) {
                                        $product = Products::findFirst($detail->material_id);
                                        $movementDetailexit = new MovementDetails();
                                        $tx = $this->transactions->get();
                                        $movementDetailexit->setTransaction($tx);
                                        $movementDetailexit->movement_id = $exitMovement->id;
                                        $movementDetailexit->product_id = $detail->material_id;
                                // $movementDetail->bag_id = $detail['bag_id'];
                                        $movementDetailexit->qty = $detail->total_necesario;
                                        if (!$product->active || !$movementDetailexit->create()) {
                                            $this->content['error'] = Helpers::getErrors($movementDetailexit);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                            $tx->rollback();
                                        }
                                    }

                                $tx = $this->transactions->get();
                                $movementId= $exitMovement->id;
                                $movement = Movements::findFirst($movementId);
                                $movement->setTransaction($tx);
                                $movement->status = 'EJECUTADO';
                                $movement->ejecute_date = date("Y-m-d H:i:s");
                                if ($movement->update()) {
                                    $movement2 = Movements::findFirst("movement_id = $movementId");
                                    if($movement2){
                                        $movement2->setTransaction($tx);
                                        $movement2->status = 'EJECUTADO';
                                        $movement2->ejecute_date = date("Y-m-d H:i:s");
                                        $movement2->update();  
                                    }
                                        $idIn = $movementId-1;
                                        $movement3 = Movements::findFirst("id = $idIn AND type_id = 5");
                                    if($movement3){
                                        $movement3->setTransaction($tx);
                                        $movement3->status = 'EJECUTADO';
                                        $movement3->ejecute_date = date("Y-m-d H:i:s");
                                        $movement3->update(); 
                                    }
                                    $this->content['result'] = true;
                                    $this->content['movement'] = $movement;
                                    $this->content['message'] = Message::success('El movimiento ha sido ejecutado');
                                    $tx->commit();
                                } else {
                                    $this->content['error'] = Helpers::getErrors($movement);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento');
                                    $tx->rollback();
                                }

                                $tx = $this->transactions->get();
                                $order = ProductionOrders::findFirst($bom_by_producto[0]->order_id);
                                $order->setTransaction($tx);
                                $order->status = 'INICIADO';
                                if($order->update()){
                                     $this->content['result'] = true;
                                    $this->content['movement'] = $order;
                                    $this->content['message'] = Message::success('Orden EJECUTADA');
                                    $tx->commit();

                                } else{
                                    $this->content['error'] = Helpers::getErrors($order);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar la orden');
                                    $tx->rollback();
                                }
                            /*foreach ($bom_by_producto as $detail) {
                                $product = Products::findFirst($detail->material_id);
                                $movementDetailentry = new MovementDetails();
                                $tx = $this->transactions->get();
                                $movementDetailentry->setTransaction($tx);
                                $movementDetailentry->movement_id = $entryMovement->id;
                                $movementDetailentry->product_id = $detail->material_id;
                                // $movementDetail->bag_id = $detail['bag_id'];
                                $movementDetailentry->qty = $detail->total_necesario;
                                if (!$product->active || !$movementDetailentry->create()) {
                                    $this->content['error'] = Helpers::getErrors($movementDetailentry);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                    $tx->rollback();
                                }
                            }*/
                                    $tx->commit();


                                } else {
                                    $this->content['error'] = Helpers::getErrors($lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                                    $tx->rollback();
                                }
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($lot);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['error'] = Helpers::getErrors($lot);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la transferencia.');
                        $tx->rollback();
                    }
                    // $this->content['result'] = true;
                    // $this->content['message'] = Message::success('PRODUCCIÓN ');

                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('Componentes Insuficientes: '.$product);
                    $this->content['cantidad_suficiente'] = $cantidades_suficientes;
                }
            } else {
                $this->content['result'] = false;
                $this->content['message'] = Message::success('No existe el bom de este producto');
            }
            // print( "<pre>".print_r( $bom_by_producto, true)."</pre>");
            // exit();


               
                }

        } catch (Exception $e) {

            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }



    public function finalizeOrder ($orden_id) {
        try {
            if ($this->userHasPermission()) {
                $request = $this->request->getPost();
                $tx = $this->transactions->get();
                $order = ProductionOrders::findFirst(intval($orden_id));
                $order->setTransaction($tx);
                $order->status = 'FINALIZADO';
                if($order->update()){
                    $this->content['result'] = true;
                    $this->content['movement'] = $order;
                    $this->content['message'] = Message::success('Orden Finalizada');
                    $tx->commit();
               }
           }

        } catch (Exception $e) {

            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }


    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();
                $sql = "SELECT order_number FROM prd_orders ORDER BY order_number DESC LIMIT 1;";
                    $lastNumber = isset($this->db->query($sql)->fetch()['order_number']) ? $this->db->query($sql)->fetch()['order_number'] : 0;
                    $order = new ProductionOrders();
                    $order->setTransaction($tx);
                    $order->production_date = $request['production_date'];
                    if ($lastNumber < (date("Y").'0001')) {
                        $order->order_number = (date("Y").'0001');
                    } else {
                        $order->order_number = ++$lastNumber;
                    }
                    if ($order->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La orden de producción ha sido creada con el número de orden: '.$order->order_number);
                        $this->content['order'] = $order;
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($order);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la orden de producción.');
                        $tx->rollback();
                    }
                if($this->content['result'] == true){
                $tx->commit();
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }


      public function createorders ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();
               
              for ($i=0; $i < count($request['products']); $i++) { 


               //  exit();
                $product = Products::findFirst($request['products'][$i]['product']['id']);
                if ($product && $product->active) {
                    $sql = "SELECT order_number FROM prd_orders ORDER BY order_number DESC LIMIT 1;";
                        $lastNumber = isset($this->db->query($sql)->fetch()['order_number']) ? $this->db->query($sql)->fetch()['order_number'] : 0;
                        $order = new ProductionOrders();
                        $order->setTransaction($tx);
                        $order->production_date = date("Y-m-d H:i:s");
                        $order->product_id = $request['products'][$i]['product']['id'];
                        $order->qty = $request['products'][$i]['cantidad'];
                        $order->unit_id = $request['products'][$i]['unit']['value'];
                        if ($lastNumber < (date("Y").'0001')) {
                            $order->order_number = (date("Y").'0001');
                        } else {
                            $order->order_number = ++$lastNumber;
                        }
                        if ($order->create()) {
                                $successLots = true;
                                for ($j = 1; $j <= 1; $j++) {
                                    $lot = new ProductionLots();
                                    $lot->setTransaction($tx);
                                    $lot->order_id = $order->id;
                                    // $lot->start_date = $order->production_date;
                                    $lot->product_id = $order->product_id;
                                    $lot->weight = $order->qty;
                                    $lot->scheduled_start_date = $order->production_date;
                                    $lot->lot_number = $order->order_number.'-0'.$j;
                                    $product_id=$order->product_id;
                                    $qty = $order->qty;
                                    if ($lot->create()) {

                                 

                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('La orden de producción ha sido creada con el número de orden: '.$order->order_number);
                                        $this->content['order'] = $order;
                                        
                                        // $tx->commit();
                                    }else {
                                        $this->content['error'] = Helpers::getErrors($order);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la orden de producción.');
                                        $tx->rollback();
                                    }

                                }
                           $this->content['result'] = true;
                            $this->content['message'] = Message::success('La orden de producción ha sido creada con el número de orden: '.$order->order_number);
                            $this->content['order'] = $order;
                            // $tx->commit();
                        } else {
                            $this->content['result'] = false;
                            $this->content['error'] = Helpers::getErrors($order);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la orden de producción.');
                            $tx->rollback();
                        }



                } else {
                    $this->content['message'] = Message::error('El producto está inactivo.');
                }
                // $tx->commit();
                }
                if($this->content['result'] == true){
                $tx->commit();
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
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();
                    $order = ProductionOrders::findFirst($id);
                    $request = $this->request->getPut();
                   
                    $product_aux =0;
                    if(is_array($request['product'])){
                        $product_aux = intval($request['product']['value']);
                    }else{
                        $product_aux = intval($request['product']);
                    }
                    $product = Products::findFirst(intval($product_aux));
                    if ($product && $product->active) {
                        if ($order) {
                            if ($order->status == 'NUEVO') {
                                        $sql="SELECT count(order_id)as tot from prd_lots where order_id = $id";
                                        $lotes = $this->db->query($sql)->fetch();
                                        if($lotes['tot']== 1){
                                        $order->setTransaction($tx);
                                        $order->product_id = intval($product_aux);
                                        $order->production_date = date("Y-m-d",strtotime($request['production_date']));
                                        $order->qty = $request['qty'];
                                        $order->unit_id = $request['unit_id'];
                                        $qty = $request['qty'];

                                        if ($order->update()) {
                                            $Plot = ProductionLots::findFirst("order_id = $id");
                                            if($Plot){
                                                $tx = $this->transactions->get();
                                                $Plot->setTransaction($tx);
                                                $Plot->product_id=$product_aux;
                                                $Plot->weight=$request['qty'];
                                                $Plot->scheduled_start_date=$request['production_date'];
                                                if ($Plot->update()) {
                                          
                                            $this->content['result'] = true;
                                        $this->content['message'] = Message::success('La orden de producción ha sido Modificada');
                                        $this->content['order'] = $order;
                                                }else{
                                                    $this->content['error'] = Helpers::getErrors($Plot);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la orden de producción.');
                                                    $tx->rollback();
                                                }

                                            }
                                            
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($order);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la orden de producción.');
                                            $tx->rollback();
                                        }

                                        }else {
                                             $this->content['result'] = false;
                                            $this->content['message'] = Message::success('La orden de producción no puede ser modificada.');
                                        }
                            } else {
                                $this->content['message'] = Message::error('No se puede modificar la información de la orden de producción.');
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
                    }
                    if($this->content['result'] == true){
                $tx->commit();
                }
                }
                 else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        
        $this->response->setJsonContent($this->content);
    }

    public function cancel ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();

                    $order = ProductionOrders::findFirst($id);

                    $request = $this->request->getPut();

                    if ($order) {
                        if ($order->status == 'NUEVO' || $order->status == 'FORMULADO') {
                            $lot = ProductionLots::findFirst("order_id = $order->id AND status <> 'NUEVO' AND status <> 'FORMULADO'");
                            if ($lot) {
                                $this->content['message'] = Message::error('No se puede cancelar la orden de producción.');
                            } else {
                                $order->setTransaction($tx);
                                $order->status = 'CANCELADO';

                                if ($order->update()) {
                                    $confirm = true;
                                    $lots = ProductionLots::find("order_id = $order->id");
                                    foreach ($lots as $lot) {
                                        $lot->setTransaction($tx);
                                        $lot->status = 'CANCELADO';
                                        if (!$lot->update()) {
                                            $confirm = false;
                                        }
                                    }
                                    if ($confirm) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('La orden de producción ha sido cancelada.');
                                        $tx->commit();
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar cancelar la orden de producción.');
                                        $tx->rollback();
                                    }
                                } else {
                                    $this->content['error'] = Helpers::getErrors($order);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar cancelar la orden de producción.');
                                    $tx->rollback();
                                }
                            }
                        } else {
                            $this->content['message'] = Message::error('No se puede cancelar la orden de producción.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado la orden de producción.');
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        
        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();

                    $order = ProductionOrders::findFirst($id);

                    if ($order) {
                        $order->setTransaction($tx);

                        if ($order->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La orden de producción ha sido eliminada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($order);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la orden de producción.');
                            }
                            // $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('La orden de producción no existe.');
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id válida.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 5 OR role_id = 6 OR role_id = 7)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
    public function getKardex ($startDate, $endingDate, $branchOfficeId, $storageId, $productId)
    {
        if ($this->userHasPermission()) {
            $kardexAux = $this->generateKardex($startDate, $endingDate, $branchOfficeId, $storageId, null, null, $productId);
            /*echo "<pre>";
            print_r($kardexAux);
            exit();*/
            $kardex = [];
            $tot=0; 
            $stock = 0;
       
               
                for ($j=0; $j < sizeof($kardexAux); $j++) {
                        // Si el tipo de movimiento es 1 es ENTRADA por lo que se suma la cantidad, si es 2 es una SALIDA por lo que se resta
                        if ($kardexAux[$j]['movement_type'] == 1) {
                            $stock += $kardexAux[$j]['qty'];
                        } elseif ($kardexAux[$j]['movement_type'] == 2) {
                            $stock -= $kardexAux[$j]['qty'];
                        }  elseif ($kardexAux[$j]['movement_type'] == 3) {
                            $stock = $kardexAux[$j]['qty'];
                         }  elseif ($kardexAux[$j]['movement_type'] == 4) { //Entrada
                             $stock += $kardexAux[$j]['qty'];
                        }  elseif ($kardexAux[$j]['movement_type'] == 5) { // Salida
                            $stock -= $kardexAux[$j]['qty'];
                            // Obtencion de la entrada ;                       
                        }
                    // $tot+=$stock
            }
            $tot = $stock;
        } else {
            return 0;
        }
        return $tot;
    }
    public function generateKardex ($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId)
    {
        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' ";
        $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 3 else 6 END, mdid DESC";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND m.date <= '".$eDate."'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 6 END";
        }
        if (!is_null($categoryId) && is_numeric($categoryId)) {
            $sql .= " AND l.category_id = $categoryId";
        }
        if (!is_null($lineId) && is_numeric($lineId)) {
            $sql .= " AND p.line_id = $lineId";
        }
        if (!is_null($productId) && is_numeric($productId)) {
            $sql .= " AND md.product_id = $productId";
        }

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO'";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '".$sDate."'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate."+ 1 days"));
            $sql .= " AND m.date <= '".$eDate."'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
        }
        if (!is_null($categoryId) && is_numeric($categoryId)) {
            $sql .= " AND l.category_id = $categoryId";
        }
        if (!is_null($lineId) && is_numeric($lineId)) {
            $sql .= " AND p.line_id = $lineId";
        }
        if (!is_null($productId) && is_numeric($productId)) {
            $sql .= " AND md.product_id = $productId";
        }
        $sql .= ") AS QUERY ORDER BY date ASC, $caseOrder, foli ASC";
        $data = $this->db->query($sql)->fetchAll();
        return $data;
    }



    public function getPackingListPdf ($id)
    {
        if (is_numeric($id)) {
            $order = ProductionOrders::findFirst($id);
            $product = Products::findFirst($order->product_id);
            $startDate = new DateTime($order->created);
            $finishDate = new DateTime($order->production_date);

            $pdf = new PDFPackingList('P','mm','Letter');
            $pdf->AliasNbPages();
            $pdf->SetOrderNumber($order->order_number);
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->SetFont('Arial','',8);

            $pdf->AddPage();
            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,utf8_decode("PRODUCTO: ".$product->name),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,"PIEZAS: ".$order->qty." UNIDADES: ",0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);
            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(95,10,"FECHA DE CAPTURA: ".$startDate->format('d/m/Y'),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(95,10,"FECHA PROGRAMADA: ".$finishDate->format('d/m/Y'),0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

            $lot = ProductionLots::find(array("order_id = $order->id", "order" => "id ASC"));

            foreach($lot as $k) {
                $bomstart = new DateTime($k->scheduled_start_date);
                $bomend = new DateTime($k->updated);
                $productlot = Products::findFirst($k->product_id);
                $pdf->Ln();
                $pdf->SetFont('Arial','B',15);
                $pdf->Cell(95,10,utf8_decode("LOTE: ".$k->lot_number),0,1,'L');
                $pdf->SetFont('Arial','',8);
                $pdf->Cell(95,10,utf8_decode("PRODUCTO: ".$productlot->name),0,1,'L');
                $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
                $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
                $pdf->Cell(95,10,"PIEZAS: ".$k->weight." UNIDADES.",0,1,'L');
                $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);
                $pdf->SetY($pdf->GetY()-3);
                $pdf->Cell(95,10,"INICIO: ".$bomstart->format('d/m/Y H:i'),0,1,'L');
                $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+95,$pdf->GetY()-2);
                $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
                $pdf->Cell(95,10,"FIN: ".$bomend->format('d/m/Y H:i'),0,1,'L');
                $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+195,$pdf->GetY()-2);

                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $pdf->SetWidths(array(40, 40, 40, 38, 38));
                $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
                $pdf->SetHeight(7);
                $pdf->SetDrawEdge(false);
                $pdf->SetFill(array(true, true, true, true, true));
                $pdf->SetTextColor(235,217,178);
                $pdf->SetFillColor(0);
                $pdf->Row(array(utf8_decode('CÓDIGO PRODUCTO'), utf8_decode('CATEGORÍA'), utf8_decode('PIEZA'), utf8_decode('A PRODUCIR'),utf8_decode('REQUERIDO')));
                $pdf->SetAligns(array('L', 'C', 'R', 'R', 'R'));
                $fill = false;
                $totalWeight = 0;
                $pdf->SetTextColor(0);
                $pdf->SetFillColor(235,217,178);
                $bom = Bom::find(array("product_id = $productlot->id ", "order" => "id ASC"));
                    foreach ($bom as $detail) {
                        $productbom = Products::findFirst($detail->material_id);
                        $line = Lines::findFirst("id = $productbom->line_id ");
                        $categorie = Categories::findFirst("id = $line->category_id ");
                        $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill));
                        $pdf->Row(array(utf8_decode($productbom->code),utf8_decode($categorie->name), number_format($detail->amount,2), number_format($k->weight, 2, '.', ','),number_format(($k->weight * $detail->amount), 2, '.', ',')));
                        $fill = !$fill;
                    }
                }
                $pdf->SetTitle("Packing List Lote #",true);
                $pdf->Output('I', "Packing List Lote #", true);

                $response = new Phalcon\Http\Response();
                $response->setHeader("Content-Type", "application/pdf");
                $response->setHeader("Content-Disposition", 'inline; filename="Packing List Lote #"');
                return $response;

        }
        return null;
    }

    public function getHandiWorkPdf ($id) {
        if (is_numeric($id)) {
            $order = ProductionOrders::findFirst($id);
            $product = Products::findFirst($order->product_id);
            $startDate = new DateTime($order->created);
            $finishDate = new DateTime($order->production_date);

            $pdf = new PDFHandiWork('L','mm','Letter');
            $pdf->AliasNbPages();
            $pdf->SetOrderNumber($order->order_number);
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->SetFont('Arial','',10);

            $pdf->AddPage();
            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(80,10,utf8_decode("PRODUCTO: ".$product->name),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+55,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(80,10,"PIEZAS: ".$order->qty." UNIDADES: ",0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+120,$pdf->GetY()-2);
            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(80,10,"FECHA DE CAPTURA: ".$startDate->format('d/m/Y'),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+55,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
            $pdf->Cell(80,10,"FECHA PROGRAMADA: ".$finishDate->format('d/m/Y'),0,1,'L');
            $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+120,$pdf->GetY()-2);

            $lot = ProductionLots::find(array("order_id = $order->id", "order" => "id ASC"));

            foreach($lot as $k) {
                $bomstart = new DateTime($k->scheduled_start_date);
                $bomend = new DateTime($k->updated);
                $productlot = Products::findFirst($k->product_id);
                $pdf->Ln();
                $pdf->SetFont('Arial','B',16);
                $pdf->Cell(80,10,utf8_decode("LOTE: ".$k->lot_number),0,1,'L');
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(80,10,utf8_decode("PRODUCTO: ".$productlot->name),0,1,'L');
                $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+55,$pdf->GetY()-2);
                $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
                $pdf->Cell(80,10,"PIEZAS: ".$k->weight." UNIDADES.",0,1,'L');
                $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+120,$pdf->GetY()-2);
                $pdf->SetY($pdf->GetY()-3);
                $pdf->Cell(80,10,"INICIO: ".$bomstart->format('d/m/Y H:i'),0,1,'L');
                $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+55,$pdf->GetY()-2);
                $pdf->SetXY($pdf->GetX()+100, $pdf->GetY()-10);
                $pdf->Cell(80,10,"FIN: ".$bomend->format('d/m/Y H:i'),0,1,'L');
                $pdf->Line($pdf->GetX()+100,$pdf->GetY()-2,$pdf->GetX()+120,$pdf->GetY()-2);

                $pdf->Ln();
                $pdf->SetFont('Arial','',11);
                $pdf->SetWidths(array(40, 28, 28, 28, 28, 28, 28, 50));
                $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                $pdf->SetHeight(6);
                $pdf->SetDrawEdge(false);
                $pdf->SetFill(array(true, true, true, true, true, true, true, true));
                $pdf->SetFillColor(0);
                $pdf->SetTextColors(array([235,217,178],[235,217,178],[235,217,178],[235,217,178],[235,217,178],[235,217,178],[235,217,178],[235,217,178]));
                $pdf->Row(array(utf8_decode('NOMBRE'), utf8_decode('COSTO/h'), utf8_decode('COSTO/m'), utf8_decode('TIEMPO'),utf8_decode('TOTAL'),utf8_decode('A PRODUCIR'),utf8_decode('FINAL'),utf8_decode('OPERADOR')));
                $pdf->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
                $fill = false;
                $totalWeight = 0;

                $lotWork = HandiWorkLots::find(array("lot_id = $k->id ", "order" => "id ASC"));
                    foreach ($lotWork as $d) {
                        $employeeWork = Employees::findFirst(array("id = $d->employee_id ", "order" => "id ASC"));
                        $handiWork = HandiWork::findFirst(array("id = $d->handiwork_id ", "order" => "id ASC"));
                        //$handiWorkProduct = HandiWorkProducts::findFirst(array("handiwork_id = $handiWork->id ", "order" => "id ASC"));
                        $pdf->SetFillColor(245, 235, 35);
                        $pdf->SetFont('Arial','',8);
                        $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill));
                        $pdf->Row(array(utf8_decode($handiWork->name),number_format($d->price_hour,4), number_format($d->price_hour/60,4), number_format($d->time_job, 4, '.', ','), number_format(($d->price_hour/60)*$d->time_job, 4, '.', ','), number_format($k->weight,4), number_format((($d->price_hour/60)*$d->time_job)*$k->weight,4), utf8_decode($employeeWork->name.' '.$employeeWork->paternal.' '.$employeeWork->mathers)));
                        $fill = !$fill;
                    }
                }
                $pdf->SetTitle("Packing List Lote #",true);
                $pdf->Output('I', "Packing List Lote #", true);

                $response = new Phalcon\Http\Response();
                $response->setHeader("Content-Type", "application/pdf");
                $response->setHeader("Content-Disposition", 'inline; filename="Packing List Lote #"');
                return $response;

        }
        return null;
    }

    
    public function getCostPdf ($id) {
        if (is_numeric($id)) {
            $order = ProductionOrders::findFirst($id);
            $product = Products::findFirst($order->product_id);
            $startDate = new DateTime($order->created);
            $finishDate = new DateTime($order->production_date);

            $costPFP=0;
            $costPF=0;

            $pdf = new PDFCost('L','mm','Letter');
            $pdf->AliasNbPages();
            $pdf->SetOrderNumber($order->order_number);
            $pdf->SetAutoPageBreak(true, 20);
            $pdf->SetFont('Arial','',10);

            $pdf->AddPage();
            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(80,10,utf8_decode("PRODUCTO: ".$product->name),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+55,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+60, $pdf->GetY()-10);
            $pdf->Cell(80,10,"PIEZAS: ".$order->qty." UNIDADES: ",0,1,'L');
            $pdf->Line($pdf->GetX()+60,$pdf->GetY()-2,$pdf->GetX()+120,$pdf->GetY()-2);
            $pdf->SetY($pdf->GetY()-3);
            $pdf->Cell(80,10,"FECHA DE CAPTURA: ".$startDate->format('d/m/Y'),0,1,'L');
            $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+55,$pdf->GetY()-2);
            $pdf->SetXY($pdf->GetX()+60, $pdf->GetY()-10);
            $pdf->Cell(80,10,"FECHA PROGRAMADA: ".$finishDate->format('d/m/Y'),0,1,'L');
            $pdf->Line($pdf->GetX()+60,$pdf->GetY()-2,$pdf->GetX()+120,$pdf->GetY()-2);
            $aux1=0;
            $aux2=0;
            $lot = ProductionLots::findFirst(array("order_id = $order->id", "order" => "id ASC"));
              $totalWeight = 0;
                $lotWork1 = HandiWorkLots::find(array("lot_id = $lot->id ", "order" => "id ASC"));
                $totHW = 0;
                    foreach ($lotWork1 as $d) {
                        $handiWork1 = HandiWork::findFirst(array("id = $d->handiwork_id ", "order" => "id ASC"));
                        $handiWorkProduct1 = HandiWorkProducts::findFirst(array("handiwork_id = $handiWork1->id ", "order" => "id ASC"));
                        $sql="SELECT plw.lot_id,plw.handiwork_id,plw.qty,plw.amount,TRUNC(plw.price_hour,5) as price_hour,TRUNC((plw.price_hour/60),5) as price_minute,plw.time_job,TRUNC(((plw.price_hour/60)*plw.time_job),5) as cost_work,TRUNC((((plw.price_hour/60)*plw.time_job) * qty),5) as  amount_real,wh.name
                            from prd_lot_works as plw
                            left join wms_handiwork as wh on plw.handiwork_id = wh.id
                            where plw.lot_id =$lot->id and plw.handiwork_id=  $handiWork1->id";
                        $data = $this->db->query($sql)->fetchAll();
  
                        $totHW+= $data[0]['amount_real'];
                    }
                    $totMP = 0;
                $tot=0;
                //$lot = ProductionLots::find(array("order_id = $order->id", "order" => "id ASC"));
                 foreach($lot as $k) {
                $productlot = Products::findFirst($lot->product_id);
                $bom = Bom::find(array("product_id = $productlot->id ", "order" => "id ASC"));
                    foreach ($bom as $detail) {
                        $productbom = Products::findFirst($detail->material_id);
                        $mi =$detail->material_id;
                        $line = Lines::findFirst("id = $productbom->line_id ");
                        $categorie = Categories::findFirst("id = $line->category_id ");
                        $sql = "SELECT distinct wmp.product_id, COALESCE(unit_price,0) as price, date
                                  from wms_products as wp left join wms_movement_details as wmp on wmp.product_id = wp.id 
                                  left join wms_movements as wm on wm.id = wmp.movement_id 
                                  where (type_id = 1 or type_id = 3) and wm.status ='EJECUTADO' And wp.id = $mi
                                  group by wmp.product_id,unit_price, date
                                  order by wmp.product_id, date desc
                                  limit 1";
                        $price = $this->db->query($sql)->fetchAll();
                        $totMP += number_format($price[0]['price'],4);
                        $tot += $price[0]['price']*($lotWork1[0]->qty * $detail->amount);
                        // $price[0]['price']*($lotWork1[0]->qty * $detail->amount)
                    }
                $aux1 =  $tot+ $totHW;
                $aux2 = $aux1/$lotWork1[0]->qty;
                }
                
                

                $pdf->SetXY($pdf->GetX()+130,$pdf->GetY()-10);
                $pdf->Cell(80,10,utf8_decode("COSTO TOTAL: $").number_format($aux1,4),0,1,'L');
                $pdf->Line($pdf->GetX()+130,$pdf->GetY()-2,$pdf->GetX()+192,$pdf->GetY()-2);

                $pdf->SetXY($pdf->GetX()+195, $pdf->GetY()-10);
                $pdf->Cell(80,10,utf8_decode("COSTO POR PIEZA: $").number_format($aux2,4),0,1,'L');
                $pdf->Line($pdf->GetX()+195,$pdf->GetY()-2,$pdf->GetX()+257,$pdf->GetY()-2);

             $lot1 = ProductionLots::find(array("order_id = $order->id", "order" => "id ASC"));

            // $costPFP=0;
            // $costPF=0;

            foreach($lot1 as $k) {

                $aux1=0;
                $aux2=0;
                $x=0;
                $y=0;
                $bomstart = new DateTime($k->scheduled_start_date);
                $bomend = new DateTime($k->updated);
                $productlot = Products::findFirst($k->product_id);
                $pdf->Ln();
                $pdf->SetFont('Arial','B',16);
                $pdf->Cell(80,10,utf8_decode("LOTE: ".$k->lot_number),0,1,'L');
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(80,10,utf8_decode("PRODUCTO: ".$productlot->name),0,1,'L');
                $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+55,$pdf->GetY()-2);
                $pdf->SetXY($pdf->GetX()+60, $pdf->GetY()-10);
                $pdf->Cell(80,10,"PIEZAS: ".$k->weight." UNIDADES.",0,1,'L');
                $pdf->Line($pdf->GetX()+60,$pdf->GetY()-2,$pdf->GetX()+120,$pdf->GetY()-2);
                $x=$pdf->GetX();
                $y=$pdf->GetY();
                

                // $pdf->SetY($pdf->GetY()-3);
                $pdf->Cell(80,10,"INICIO: ".$bomstart->format('d/m/Y H:i'),0,1,'L');
                $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+55,$pdf->GetY()-2);
                $pdf->SetXY($pdf->GetX()+60, $pdf->GetY()-10);
                $pdf->Cell(80,10,"FIN: ".$bomend->format('d/m/Y H:i'),0,1,'L');
                $pdf->Line($pdf->GetX()+60,$pdf->GetY()-2,$pdf->GetX()+120,$pdf->GetY()-2);
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(80,10,"MANO DE OBRA",0,1,'L');
                $pdf->SetFont('Arial','',10);
                $pdf->SetWidths(array(53, 34, 30, 30, 30, 25,25, 30));
                $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C','C', 'C'));
                $pdf->SetHeight(6);
                $pdf->SetDrawEdge(false);
                $pdf->SetFill(array(true, true, true, true, true, true, true,true));
                $pdf->SetFillColor(249, 203, 16);
                $pdf->Row(array(utf8_decode('NOMBRE'), utf8_decode('COSTO/h'), utf8_decode('COSTO/m'), utf8_decode('TIEMPO'),utf8_decode('TOTAL'),utf8_decode('A PRODUCIR'),utf8_decode('PRODUCIDO'),utf8_decode('FINAL')));
                $pdf->SetAligns(array('L', 'R', 'R', 'R', 'R', 'R', 'R', 'R'));
                $fill = false;
                $totalWeight = 0;

                $lotWork = HandiWorkLots::find(array("lot_id = $k->id ", "order" => "id ASC"));
                $totHW = 0;
                    foreach ($lotWork as $d) {
                        $handiWork = HandiWork::findFirst(array("id = $d->handiwork_id ", "order" => "id ASC"));
                        $handiWorkProduct = HandiWorkProducts::findFirst(array("handiwork_id = $handiWork->id ", "order" => "id ASC"));
                        $sql="SELECT plw.lot_id,plw.handiwork_id,plw.qty,plw.amount,TRUNC(plw.price_hour,5) as price_hour,TRUNC((plw.price_hour/60),5) as price_minute,plw.time_job,TRUNC(((plw.price_hour/60)*plw.time_job),5) as cost_work,TRUNC((((plw.price_hour/60)*plw.time_job) * qty),5) as  amount_real,wh.name
                            from prd_lot_works as plw
                            left join wms_handiwork as wh on plw.handiwork_id = wh.id
                            where plw.lot_id =$k->id and plw.handiwork_id=  $handiWork->id";
                        $data = $this->db->query($sql)->fetchAll();
  

                        $pdf->SetFillColor(245, 235, 35);
                        $pdf->SetFont('Arial','',10);
                        $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill,$fill));
                        $pdf->Row(array(utf8_decode($handiWork->name),number_format($data[0]['price_hour'],5), number_format($data[0]['price_minute'],5), number_format($data[0]['time_job'], 5, '.', ','), number_format($data[0]['cost_work'], 5, '.', ','), number_format($k->weight,2),number_format($k->qty_real,2), number_format($data[0]['amount_real'],5)));
                        $fill = !$fill;
                        $totHW+= $data[0]['amount_real'];
                    }
                $pdf->SetX($pdf->GetX()+229);
                $pdf->Cell(28,5,'$ '.number_format($totHW,5),0,1,'R');

                $pdf->Ln();
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(80,10,"BOM",0,1,'L');
                $productlot = Products::findFirst($k->product_id);
                $pdf->SetFont('Arial','',10);
                $pdf->SetWidths(array(40, 48, 35, 25,25, 35,24,24));
                $pdf->SetAligns(array('C', 'C', 'C', 'C','C', 'C','C','C'));
                $pdf->SetHeight(6);
                $pdf->SetDrawEdge(false);
                $pdf->SetFill(array(true, true, true, true, true, true, true,true));
                $pdf->SetFillColor(249, 203, 16);
                $pdf->Row(array(utf8_decode('CÓDIGO PRODUCTO'), utf8_decode('CATEGORÍA'), utf8_decode('PIEZA'), utf8_decode('A PRODUCIR'), utf8_decode('PRODUCIDO'),utf8_decode('REQUERIDO'),utf8_decode('PRECIO'),utf8_decode('TOTAL')));
                $pdf->SetAligns(array('L', 'C', 'R', 'R','R', 'R','R','R'));
                $fill = false;
                $totalWeight = 0;

                $pdf->SetFillColor(245, 235, 35);
                $totMP = 0;
                $tot=0;
                $bom = Bom::find(array("product_id = $productlot->id ", "order" => "id ASC"));
                    foreach ($bom as $detail) {
                        $productbom = Products::findFirst($detail->material_id);
                        $mi =$detail->material_id;
                        $line = Lines::findFirst("id = $productbom->line_id ");
                        $categorie = Categories::findFirst("id = $line->category_id ");
                        $sql = "SELECT distinct wmp.product_id, COALESCE(unit_price,0) as price, date
                                  from wms_products as wp left join wms_movement_details as wmp on wmp.product_id = wp.id 
                                  left join wms_movements as wm on wm.id = wmp.movement_id 
                                  where (type_id = 1 or type_id = 3) and wm.status ='EJECUTADO' And wp.id = $mi
                                  group by wmp.product_id,unit_price, date
                                  order by wmp.product_id, date desc
                                  limit 1";
                        $price = $this->db->query($sql)->fetchAll();
                        $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill, $fill,$fill));
                        $pdf->Row(array(utf8_decode($productbom->code),utf8_decode($categorie->name), number_format($detail->amount,4), number_format($k->weight, 2, '.', ','),number_format($k->qty_real, 2, '.', ','),number_format(($k->weight * $detail->amount), 4, '.', ','),$price[0]['price'],
                            number_format(($price[0]['price']*($k->qty_real * $detail->amount)),4)));
                        $fill = !$fill;
                        $totMP += number_format($price[0]['price'],4);
                        $tot += $price[0]['price']*($k->qty_real * $detail->amount);
                        //
                    }
                $aux1 =  $tot+ $totHW;
                if($k->qty_real != null || $k->qty_real != 0){
                    $aux2 = $aux1/$k->qty_real;
                }else {
                     $aux2 = $aux1/$k->weight;
                }
                
                $pdf->SetX($pdf->GetX()+208);
                $pdf->Cell(24,5,'$ '.number_format($totMP,5),0,1,'R');
                $pdf->SetXY($pdf->GetX()+232,$pdf->GetY()-5);
                $pdf->Cell(24,5,'$ '.number_format($tot,5),0,1,'R');
           
                $x2=$pdf->GetX();
                $y2=$pdf->GetY();

                $pdf->SetXY($x+130, $y-10);
                $pdf->Cell(80,10,utf8_decode("COSTO TOTAL: $").number_format($aux1,4),0,1,'L');
                $pdf->Line($pdf->GetX()+130,$pdf->GetY()-2,$pdf->GetX()+192,$pdf->GetY()-2);

                $pdf->SetXY($x+195, $y-10);
                $pdf->Cell(80,10,utf8_decode("COSTO POR PIEZA: $").number_format($aux2,4),0,1,'L');
                $pdf->Line($pdf->GetX()+195,$pdf->GetY()-2,$pdf->GetX()+257,$pdf->GetY()-2);

                $pdf->SetXY($x2,$y2+40);
                $pdf->Ln();

                $costPF +=  $aux1 ;
                }
                $costPFP = $costPF/$order->qty;


                $pdf->SetTitle("COSTO DE PRODUCCION #",true);
                $pdf->Output('I', "Costeo #", true);

                $response = new Phalcon\Http\Response();
                $response->setHeader("Content-Type", "application/pdf");
                $response->setHeader("Content-Disposition", 'inline; filename="Packing List Lote #"');
                return $response;

        }
        return null;
    }
}

class PDFPackingList extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $orderNumber;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo.png';
        $this->Image($img,10,10,45,0,'PNG');
        $this->SetFont('Arial','B',17);
        $this->SetX($this->GetX()+78);
        $this->MultiCell(130, 12, utf8_decode("ORDEN DE PRODUCCIÓN: $this->orderNumber"), 0, 'C', false);
        $this->SetFont('Arial','B',20);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(260);
        $this->Cell(195, 6, "WWW.EMPRESA.COM", 0, 0, 'C', false);
        $this->SetFont('Arial', '', 10);
        $this->SetY(274);
        $this->SetFillColor(235,217,178);
        $this->SetTextColor(0);
        $this->Rect(0,268,216,190,'DF');
        $this->Cell(0,0,utf8_decode('Página '.$this->PageNo().' de {nb}'),0,0,'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        $this->aligns=$a;
    }

    function SetHeight($h)
    {
        $this->height=$h;
    }

    function SetOrderNumber($o)
    {
        $this->orderNumber=$o;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function Row($data)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=$this->height*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f=isset($this->fill[$i]) ? $this->fill[$i] : false;
            $x=$this->GetX();
            $y=$this->GetY();
            if ($this->drawEdge) {
                $this->Rect($x,$y,$w,$h);
            }
            $this->MultiCell($w,$this->height,$data[$i],0,$a,$f);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}

class PDFHandiWork extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $orderNumber;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo.png';
        $this->Image($img,10,10,45,0,'PNG');
        $this->SetFont('Arial','B',17);
        $this->SetX($this->GetX()+141);
        $this->MultiCell(130, 12, utf8_decode("ORDEN DE PRODUCCIÓN: $this->orderNumber"), 0, 'C', false);
        $this->SetFont('Arial','B',14);
        $this->SetX($this->GetX()+153);
        $this->Cell(130, 2, utf8_decode("MANO DE OBRA"), 0, 'C', false);
        $this->Cell(130, 10, 0, 'C', false);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(260);
        $this->Cell(195, 6, "www.panelw.com", 0, 0, 'C', false);
        $this->SetFont('Arial', '', 10);
        $this->SetY(274);
        $this->SetFillColor(249, 203, 16);
        $this->SetTextColor(0);
        $this->Rect(0,268,216,190,'DF');
        $this->Cell(0,0,utf8_decode('Página '.$this->PageNo().' de {nb}'),0,0,'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        $this->aligns=$a;
    }

    function SetHeight($h)
    {
        $this->height=$h;
    }

    function SetOrderNumber($o)
    {
        $this->orderNumber=$o;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }
    function SetTextColors($tc)
    {
        $this->textColors=$tc;
    }

    function Row($data)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=$this->height*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f=isset($this->fill[$i]) ? $this->fill[$i] : false;
            $x=$this->GetX();
            $y=$this->GetY();
            if ($this->drawEdge) {
                $this->Rect($x,$y,$w,$h);
            }
            if (is_array($this->textColors) && isset($this->textColors[$i])) {
                if (is_numeric($this->textColors[$i])) {
                    $this->SetTextColor($this->textColors[$i]);
                } elseif (is_array($this->textColors[$i])) {
                    $this->SetTextColor($this->textColors[$i][0], $this->textColors[$i][1], $this->textColors[$i][2]);
                } else {
                    $this->SetTextColor(0);
                }
            } else {
                $this->SetTextColor(0);
            }
            $this->MultiCell($w,$this->height,$data[$i],0,$a,$f);
            $this->SetXY($x+$w,$y);
        }
    }

    function CheckPageBreak($h)
    {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}

class PDFCost extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $orderNumber;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $img = $path . 'logo.png';
        $this->Image($img,10,10,65,0,'PNG');
        $this->SetFont('Arial','B',17);
        $this->SetX($this->GetX()+141);
        $this->MultiCell(130, 12, utf8_decode("ORDEN DE PRODUCCIÓN: $this->orderNumber"), 0, 'C', false);
        $this->SetFont('Arial','B',14);
        $this->SetX($this->GetX()+153);
        $this->Cell(130, 2, utf8_decode("COSTO DE PRODUCCIÓN"), 0, 'C', false);
        $this->Cell(130, 10, 0, 'C', false);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(260);
        $this->Cell(195, 6, "WWW.EMPRESA.COM", 0, 0, 'C', false);
        $this->SetFont('Arial', '', 10);
        $this->SetY(274);
        $this->SetFillColor(249, 203, 16);
        $this->SetTextColor(0);
        $this->Rect(0,268,216,190,'DF');
        $this->Cell(0,0,utf8_decode('Página '.$this->PageNo().' de {nb}'),0,0,'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        $this->aligns=$a;
    }

    function SetHeight($h)
    {
        $this->height=$h;
    }

    function SetOrderNumber($o)
    {
        $this->orderNumber=$o;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge=$de;
    }

    function SetFill($f)
    {
        $this->fill=$f;
    }

    function Row($data)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=$this->height*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f=isset($this->fill[$i]) ? $this->fill[$i] : false;
            $x=$this->GetX();
            $y=$this->GetY();
            if ($this->drawEdge) {
                $this->Rect($x,$y,$w,$h);
            }
            $this->MultiCell($w,$this->height,$data[$i],0,$a,$f);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}