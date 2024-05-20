<?php

use Phalcon\Mvc\Controller;

class InvoiceInBulkDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getInvoiceInBulkDetailsByInvoiceId ($invoiceId)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($invoiceId)) {
                $sql = "SELECT det.id, det.invoice_id, det.product_id, det.qty, prod.name AS product, det.unit_price, (det.unit_price * det.qty) AS total_price, det.packages_qty
                        FROM sls_invoice_in_bulk_details AS det
                        LEFT JOIN wms_products AS prod ON prod.id = det.product_id
                        WHERE det.invoice_id = $invoiceId;";
                $this->content['invoiceDetails'] = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido una venta de fibra correcta.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getInvoiceInBulkDetail ($id)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $this->content['invoiceDetail'] = InvoiceInBulkDetails::findFirst($id);
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id válido.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        if ($this->userHasPermission()) {
            try {
                $request = $this->request->getPost();
                if (isset($request['invoice_id']) && is_numeric($request['invoice_id']) && isset($request['product_id']) && is_numeric($request['product_id']) && isset($request['qty']) && is_numeric($request['qty'])) {
                    $product = Products::findFirst($request['product_id']);
                    if ($product && $product->active) {
                        $invoice = Invoices::findFirst($request['invoice_id']);
                        if ($invoice) {
                            $movement = Movements::findFirst($invoice->in_bulk_movement_id);
                            if ($movement) {
                                if ($movement->status == 0) {
                                    $sql = "SELECT s1.product_id, SUM(s1.qty) AS qty
                                            FROM (
                                                SELECT md.id, md.movement_id, md.product_id, CASE WHEN m.type = 2 THEN md.qty * -1 ELSE md.qty END AS qty, m.date
                                                FROM wms_movement_details AS md
                                                INNER JOIN wms_movements AS m
                                                ON m.id = md.movement_id
                                                INNER JOIN wms_storages AS s
                                                ON s.id = m.storage_id
                                                WHERE s.storage_type_id = 2
                                                AND s.id = $movement->storage_id
                                                AND m.status = 1
                                                ORDER BY date ASC
                                            ) AS s1
                                            WHERE s1.product_id = ".$request['product_id']."
                                            GROUP BY s1.product_id;";
                                    $availableProduct = $this->db->query($sql)->fetch();
                                    if ($availableProduct) {
                                        if ($availableProduct['qty'] >= $request['qty']) {
                                            $customerBranchOffice = CustomerBranchOffices::findFirst($invoice->customer_branch_office_id);
                                            $customer = Customers::findFirst($customerBranchOffice->customer_id);
                                            $productPrice = ProductsPrices::findFirst("product_id = ".$request['product_id']." AND price_level = '$customer->price_list'");
                                            $tx = $this->transactions->get();
                                            $invoiceDetail = new InvoiceInBulkDetails();
                                            $invoiceDetail->setTransaction($tx);
                                            $invoiceDetail->invoice_id = $request['invoice_id'];
                                            $invoiceDetail->product_id = $request['product_id'];
                                            $invoiceDetail->qty = $request['qty'];
                                            if (isset($request['packages_qty']) && is_numeric($request['packages_qty']) && $request['packages_qty'] >= 0 && $request['packages_qty'] <= 32767) {
                                                $invoiceDetail->packages_qty = $request['packages_qty'];
                                            }
                                            if ($productPrice && is_numeric($productPrice->price)) {
                                                $invoiceDetail->unit_price = $productPrice->price;
                                            } else {
                                                $invoiceDetail->unit_price = 0;
                                            }
                                            if ($invoiceDetail->create()) {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('Detalle registado correctamente.');
                                                $tx->commit();
                                            } else {
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el detalle.');
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('No se tienen existencias suficientes del producto seleccionado.');
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('No se tienen existencias del producto seleccionado.');
                                    }
                                }
                            } else {
                                $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $invoiceDetail = InvoiceInBulkDetails::findFirst($id);
                // print_r($invoiceDetail->product_id);
                // exit();
                $cnd =false;
                if ($invoiceDetail) {
                    $invoice = Invoices::findFirst($invoiceDetail->invoice_id);
                    if ($invoice) {
                        // $movement = Movements::findFirst($invoice->in_bulk_movement_id);
                        if($invoice->status=='REMISIONADO'){
                            // $invoiceDetail->setTransaction($tx);
                             $invoiceDetail->setTransaction($tx);
                            if ($invoiceDetail->delete()) {
                                    $cnd =true;
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('El detalle ha sido eliminado.');
                                    // $tx->commit();
                                } else {
                                    $cnd =false;
                                    $this->content['result'] = false;
                                    $this->content['error'] = Helpers::getErrors($invoiceDetail);
                                    if ($this->content['error'][1]) {
                                        $this->content['message'] = Message::error($this->content['error'][1]);
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la venta de fibra.');
                                    }
                                }

                        }elseif($invoice->status=='ENVIADO') {

                            $mov = MovementDetails::findFirst("product_id = ".$invoiceDetail->product_id." AND movement_id = $invoice->in_bulk_movement_id");
                              $mov->setTransaction($tx);
                                if ($mov->delete()) {
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('El detalle ha sido eliminado.');
                                    // $tx->commit();
                                    $cnd =true;
                                } else {
                                    $cnd =false;
                                    $this->content['false'] = true;
                                    $this->content['error'] = Helpers::getErrors($invoiceDetail);
                                    if ($this->content['error'][1]) {
                                        $this->content['message'] = Message::error($this->content['error'][1]);
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la venta de fibra.');
                                    }
                                }
                                  $invoiceDetail->setTransaction($tx);
                                  if ($invoiceDetail->delete()) {
                                    $cnd =true;
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('El detalle ha sido eliminado.');
                                    // $tx->commit();
                                } else {
                                    $cnd =false;
                                    $this->content['result'] = false;
                                    $this->content['error'] = Helpers::getErrors($invoiceDetail);
                                    if ($this->content['error'][1]) {
                                        $this->content['message'] = Message::error($this->content['error'][1]);
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la venta de fibra.');
                                    }
                                }
                        }
                        //if ($movement) {
                            //if ($movement->status == 0) {
                        
                                
                            /*} else {
                                $this->content['message'] = Message::error('No se puede eliminar el detalle debido a que la venta de fibra ya fue entregada.');
                            }*/
                        /*} else {
                            $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                        }*/
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                    }
                }
                if($this->content['result'] == true){
                $tx->commit();
            } else{
                    $tx->rolback();
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se pudo eliminar el detalle de  la remision y movimientos que depende de la remision.');
            }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

        public function editinBulksDetails ()
        {
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $idDetail = $request['detailId'];
            $newQty = $request['newQty'];
            $bulks = intval($request['bulks']);
            $invoiceinBulk = InvoiceInBulkDetails::findFirst($idDetail);
            $cnd =false;
            if($request['status']=='REMISIONADO'){
            if ($invoiceinBulk) {
                $invoiceinBulk->setTransaction($tx);
                $invoiceinBulk->qty = $newQty;
                $invoiceinBulk->packages_qty = $bulks;
                if ($invoiceinBulk->update()) {
                    $cnd = true;
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('La remision ha sido modificada exitosamente.');
                    // $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($invoiceinBulk);
                    $this->content['message'] = Message::error('');
                }
            } else {
                $this->content['result'] = false;
                $this->content['message'] = Message::success('Ha ocurrido un error al intentar modificar la remision.');
            }
            if($this->content['result'] == true){
                $tx->commit();
            } else{
                    $tx->rolback();
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se pudo actualizar revisar existencias a la fecha.');
            }
            } elseif ($request['status']=='ENVIADO') {
                // se actualiza el detalle del mov
                if(is_array($request['product_id'])){
                    $product= $request['product_id']['value'];
                }else {
                    $product= $request['product_id'];           
                }
                $invoice = Invoices::findFirst($invoiceinBulk->invoice_id);
                $movimiento = Movements::findFirst($invoice->in_bulk_movement_id);
                $mov = MovementDetails::findFirst("product_id = ".$product." AND movement_id = $invoice->in_bulk_movement_id");
                 $movements = new MovementsController();       
                $auxqty=$mov->qty;   
                if($request['newQty']> $mov->qty){
                    $availableInBulkProducts = $movements->generateStorageInventoryv2byMark(null, $movimiento->storage_id, null, null, $mov->product_id ,$mov->created, null);
                     $mov->setTransaction($tx);

                if(($availableInBulkProducts[0]['stock'] > ($request['newQty'] - $auxqty)))
                {
                    $mov->qty = $request['newQty'];
                    $cnd= true;

              
                } else {
                        // $tx->rolback();
                        $this->content['result'] = false;
                        $this->content['message'] = Message::success('no hay existencias sufientes a la fecha.');
                }

            $invoiceinBulk->setTransaction($tx);
            if(($availableInBulkProducts[0]['stock'] > ($request['newQty'] - $auxqty))){
                $invoiceinBulk->qty = $newQty;
                $invoiceinBulk->packages_qty = $bulks;
                $mov->qty = $request['newQty'];
                $cnd= true;
            }else {
                $this->content['result'] = false;
                $this->content['message'] = Message::success('la cantidad exede la existencia a la fecha de la venta.');
            }
                }elseif($request['newQty']>=0 &&  $request['newQty']<=$mov->qty) {
                    $invoiceinBulk->qty = $newQty;
                    $invoiceinBulk->packages_qty = $bulks;
                    $mov->qty = $request['newQty'];
                    $cnd= true;
                }else {
                // $tx->rolback();
                $this->content['result'] = false;
                $this->content['message'] = Message::success('la cantidad debe ser mayor a 0');
            }
                // code...
            if($cnd == true){
            if ($invoiceinBulk->update()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('La remision y detalle de almacen an sido modificados correctamente.');
                    // $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($invoiceinBulk);
                    $this->content['message'] = Message::error('');
                }
            if ($mov->update()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('La remision y detalle de almacen an sido modificados correctamente.');
                    // $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($mov);
                    $this->content['message'] = Message::error('');
            }
            if($this->content['result'] == true){
                $tx->commit();
            } else{
                    $tx->rolback();
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No se pudo actualizar la remision y movimientos.');
            }
        }else {
                     //$this->content['result'] = false;
                    //$this->content['message'] = Message::success('No se pudo a.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 2 OR role_id = 4 OR role_id = 17
                        OR role_id = 20 OR role_id = 22 OR role_id = 27 OR role_id = 28 OR role_id = 29)
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
