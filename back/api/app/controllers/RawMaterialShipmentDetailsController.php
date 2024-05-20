<?php

use Phalcon\Mvc\Controller;
use Endroid\QrCode\QrCode;

class RawMaterialShipmentDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getRawMaterialShipmentDetailsByRawMaterialShipmentId ($id)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $sql = "SELECT md.id, md.product_id, p.name AS product, md.qty, md.unit_price, (md.qty * md.unit_price) AS total_price
                        FROM wms_movement_details AS md
                        INNER JOIN wms_products AS p
                        ON p.id = md.product_id
                        INNER JOIN wms_movements AS m
                        ON m.id = md.movement_id
                        INNER JOIN pur_raw_material_shipments AS rms
                        ON rms.movement_id = m.id
                        WHERE rms.id = $id
                        ORDER BY md.id ASC;";
                $details = $this->db->query($sql)->fetchAll();
                $this->content['details'] = $details;
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id de recepción MP válido.');
            }
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
                if (isset($request['rawMaterialShipmentId']) && is_numeric($request['rawMaterialShipmentId']) && isset($request['productId']) && is_numeric($request['productId']) && isset($request['qty']) && is_numeric($request['qty']) && isset($request['unitPrice']) && is_numeric($request['unitPrice'])) {
                    $product = Products::findFirst($request['productId']);
                    if ($product->active) {
                        $rawMaterialShipment = RawMaterialShipments::findFirst($request['rawMaterialShipmentId']);
                        if ($rawMaterialShipment) {
                            if ($rawMaterialShipment->status == 'NUEVO') {
                                $movement = Movements::findFirst($rawMaterialShipment->movement_id);
                                if ($movement->status == 0) {
                                    $tx = $this->transactions->get();
                                    $movementDetail = new MovementDetails();
                                    $movementDetail->setTransaction($tx);
                                    $movementDetail->movement_id = $movement->id;
                                    $movementDetail->product_id = $request['productId'];
                                    $movementDetail->qty = $request['qty'];
                                    $movementDetail->unit_price = $request['unitPrice'];
                                    if ($movementDetail->create()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('El detalle ha sido registrado correctamente.');
                                        $tx->commit();
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($movementDetail);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el detalle.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('No se pueden agregar detalles a la recepción MP indicada.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No se pueden agregar detalles a la recepción MP indicada.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se ha encontrado la recepción MP.');
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se han recibido los datos necesarios.');
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

                $detail = MovementDetails::findFirst($id);

                $request = $this->request->getPut();

                if ($detail) {
                    $productId = (isset($request['productId']) && is_numeric($request['productId'])) ? $request['productId'] : $detail->product_id;
                    $product = Products::findFirst($productId);
                    if ($product->active) {
                        $detail->setTransaction($tx);
                        if (isset($request['productId']) && is_numeric($request['productId'])) {
                            $detail->product_id = $request['productId'];
                        }
                        if (isset($request['qty']) && is_numeric($request['qty'])) {
                            $detail->qty = $request['qty'];
                        }
                        if (isset($request['unitPrice']) && is_numeric($request['unitPrice'])) {
                            $detail->unit_price = $request['unitPrice'];
                        }

                        if ($detail->update()) {
                            $this->content['detail'] = $detail;
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El detalle ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($detail);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el detalle.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('El producto está inactivo.');
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

                $movementDetail = MovementDetails::findFirst($id);

                if ($movementDetail) {
                    $movement = Movements::findFirst($movementDetail->movement_id);
                    if ($movement->status == 0) {
                        $movementDetail->setTransaction($tx);
                        if ($movementDetail->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El detalle ha sido eliminado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($movementDetail);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el detalle.');
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('No se puede eliminar el detalle.');
                    }
                } else {
                    $this->content['message'] = Message::error('El producto no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 20 OR role_id = 22)
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
