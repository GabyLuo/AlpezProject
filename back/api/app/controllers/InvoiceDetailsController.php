<?php

use Phalcon\Mvc\Controller;

class InvoiceDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getInvoiceDetailsByInvoiceId ($invoiceId)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($invoiceId)) {
                $sql = "SELECT det.id, det.invoice_id, det.bale_id, bale.product_id, bale.qty, prod.name AS product,
                               det.unit_price, (det.unit_price * bale.qty) AS total_price
                        FROM sls_invoice_details AS det
                        INNER JOIN wms_bales AS bale ON bale.id = det.bale_id
                        INNER JOIN wms_products AS prod ON prod.id = bale.product_id
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

    public function getInvoiceDetail ($id)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $this->content['invoiceDetail'] = InvoiceDetails::findFirst($id);
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
                $invoice = Invoices::findFirst($request['invoice_id']);
                $bale = Bales::findFirst($request['bale_id']);
                if ($invoice && $bale) {
                    $product = Products::findFirst($bale->product_id);
                    if ($product->active) {
                        $invoiceDetail = InvoiceDetails::findFirst("invoice_id = $invoice->id AND bale_id = $bale->id");
                        if ($invoiceDetail) {
                            $this->content['message'] = Message::error('La paca asignada ya se encuentra registrada para está Venta de fibra.');
                        } else {
                            $movement = Movements::findFirst($invoice->bale_movement_id);
                            if ($movement) {
                                if ($movement->status == 0) {
                                    if (is_numeric($request['invoice_id']) && is_numeric($request['bale_id'])) {
                                        $sql = "SELECT md.id, m.date
                                                FROM wms_bales AS b
                                                INNER JOIN wms_movement_details AS md ON md.bale_id = b.id
                                                INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                                WHERE m.type = 1 AND m.status = 1 AND m.storage_id = $movement->storage_id AND b.id = ".$request['bale_id']."
                                                ORDER BY date DESC LIMIT 1;";
                                        $baleEntryMovement = $this->db->query($sql)->fetch();
                                        if ($baleEntryMovement) {
                                            $sql = "SELECT value, product_id, qty, date
                                                    FROM (
                                                        SELECT md.bale_id AS value, md.product_id, md.qty, m.date
                                                        FROM wms_movement_details AS md
                                                        INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                                        WHERE m.status = 1
                                                        AND m.type = 2
                                                        AND md.bale_id = $bale->id
                                                        AND m.storage_id = $movement->storage_id
                                                        AND m.date >= '".$baleEntryMovement['date']."'
                                                    ) AS sub
                                                    ORDER BY date DESC LIMIT 1;";
                                            $baleExitMovement = $this->db->query($sql)->fetch();
                                            if ($baleExitMovement) {
                                                $this->content['message'] = Message::error('La paca seleccionada no se encuentra disponible.');
                                            } else {
                                                $customerBranchOffice = CustomerBranchOffices::findFirst($invoice->customer_branch_office_id);
                                                $customer = Customers::findFirst($customerBranchOffice->customer_id);
                                                $productPrice = ProductsPrices::findFirst("product_id = $bale->product_id AND price_level = '$customer->price_list'");
                                                $tx = $this->transactions->get();
                                                $invoiceDetail = new InvoiceDetails();
                                                $invoiceDetail->setTransaction($tx);
                                                $invoiceDetail->invoice_id = $request['invoice_id'];
                                                $invoiceDetail->bale_id = $request['bale_id'];
                                                $invoiceDetail->unit_price = $request['price'];
//                                                if ($productPrice && is_numeric($productPrice->price)) {
//                                                    $invoiceDetail->unit_price = $productPrice->price;
//                                                } else {
//                                                    $invoiceDetail->unit_price = 0;
//                                                }

                                                if ($invoiceDetail->create()) {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('Detalle registado correctamente.');
                                                    $tx->commit();
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($invoiceDetail);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el detalle.');
                                                    $tx->rollback();
                                                }
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('No se ha encontrado la paca seleccionada.');
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('No se han recibido venta de fibra ni paca válidos.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('No se puede agregar el detalle debido a que la venta de fibra ya fue entregada.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
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
                $invoiceDetail = InvoiceDetails::findFirst($id);
                if ($invoiceDetail) {
                    $invoice = Invoices::findFirst($invoiceDetail->invoice_id);
                    if ($invoice) {
                        $movement = Movements::findFirst($invoice->bale_movement_id);
                        if ($movement) {
                            if ($movement->status == 0) {
                                $invoiceDetail->setTransaction($tx);
                        
                                if ($invoiceDetail->delete()) {
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('El detalle ha sido eliminado.');
                                    $tx->commit();
                                } else {
                                    $this->content['error'] = Helpers::getErrors($invoiceDetail);
                                    if ($this->content['error'][1]) {
                                        $this->content['message'] = Message::error($this->content['error'][1]);
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la venta de fibra.');
                                    }
                                }
                            } else {
                                $this->content['message'] = Message::error('No se puede eliminar el detalle debido a que la venta de fibra ya fue entregada.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
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

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 27 OR role_id = 28 OR role_id = 29 OR role_id = 28 OR role_id = 17)
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
