<?php

use Phalcon\Mvc\Controller;

class InvoiceLaminateDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getInvoiceLaminateDetailsByInvoiceId ($invoiceId)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($invoiceId)) {
                $sql = "SELECT det.id, det.invoice_id, det.product_id, det.qty, prod.name AS product, det.unit_price, (det.unit_price * det.qty) AS total_price
                        FROM sls_invoice_laminate_details AS det
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

    public function getInvoiceLaminateDetail ($id)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $this->content['invoiceDetail'] = InvoiceLaminateDetails::findFirst($id);
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
                            $movement = Movements::findFirst($invoice->laminate_movement_id);
                            if ($movement) {
                                if ($movement->status == 0) {
                                    $sql = "SELECT s1.product_id, SUM(s1.qty) AS qty
                                            FROM (
                                                SELECT md.id, md.product_id, p.name AS product_name, CASE WHEN m.type = 1 THEN md.qty WHEN m.type = 2 THEN (md.qty * -1) END AS qty, m.date
                                                FROM wms_movement_details AS md
                                                INNER JOIN wms_movements AS m
                                                ON m.id = md.movement_id
                                                INNER JOIN wms_products AS p
                                                ON p.id = md.product_id
                                                INNER JOIN wms_lines AS l
                                                ON l.id = p.line_id
                                                WHERE l.category_id = 5
                                                AND m.status = 1
                                                AND m.storage_id = $movement->storage_id
                                                AND md.product_id = ".$request['product_id']."
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
                                            $invoiceDetail = new InvoiceLaminateDetails();
                                            $invoiceDetail->setTransaction($tx);
                                            $invoiceDetail->invoice_id = $request['invoice_id'];
                                            $invoiceDetail->product_id = $request['product_id'];
                                            $invoiceDetail->qty = $request['qty'];
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
                                } else {
                                    $this->content['message'] = Message::error('Ya se ha ejecutado el consumo del laminado.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se ha encontrado la venta de fibra.');
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
                if (is_numeric($id)) {
                    $tx = $this->transactions->get();
                    $invoiceDetail = InvoiceLaminateDetails::findFirst($id);
                    if ($invoiceDetail) {
                        $invoice = Invoices::findFirst($invoiceDetail->invoice_id);
                        if ($invoice) {
                            $movement = Movements::findFirst($invoice->laminate_movement_id);
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
                } else {
                    $this->content['message'] = Message::error('No se ha recibido una id de venta válida.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    public function editLaminateDetails ()
    {
        $tx = $this->transactions->get();
        $request = $this->request->getPost();
        $idDetail = $request['detailId'];
        $newQty = $request['newQty'];
        $invoiceLaminate = InvoiceLaminateDetails::findFirst($idDetail);
        if ($invoiceLaminate) {
            $invoiceLaminate->setTransaction($tx);
            $invoiceLaminate->qty = $newQty;
            if ($invoiceLaminate->update()) {
                $this->content['result'] = true;
                $this->content['message'] = Message::success('La remision ha sido modificada exitosamente.');
                $tx->commit();
            } else {
                $this->content['error'] = Helpers::getErrors($invoiceLaminate);
                $this->content['message'] = Message::error('');
            }
        } else {
            $this->content['result'] = false;
            $this->content['message'] = Message::success('Ha ocurrido un error al intentar modificar la remision.');
        }
        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 27 OR role_id = 28 OR role_id = 29)
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
