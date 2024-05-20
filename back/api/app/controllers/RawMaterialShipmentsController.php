<?php

use Phalcon\Mvc\Controller;
use Endroid\QrCode\QrCode;

class RawMaterialShipmentsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getRawMaterialShipments ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT rms.id, rms.supplier_id, sup.name AS supplier, m.storage_id, sto.name AS storage, sto.branch_office_id, bo.name AS branch_office, rms.status
                    FROM pur_raw_material_shipments AS rms
                    INNER JOIN wms_movements AS m
                    ON m.id = rms.movement_id
                    INNER JOIN pur_suppliers AS sup
                    ON sup.id = rms.supplier_id
                    INNER JOIN wms_storages AS sto
                    ON sto.id = m.storage_id
                    INNER JOIN wms_branch_offices AS bo
                    ON bo.id = sto.branch_office_id
                    ORDER BY rms.id DESC;";
            $shipments = $this->db->query($sql)->fetchAll();
            $this->content['shipments'] = $shipments;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getRawMaterialShipment ($id)
    {
        if ($this->userHasPermission()) {
            $shipment = null;
            if (is_numeric($id)) {
                $sql = "SELECT rms.id, rms.supplier_id, sup.name AS supplier, m.storage_id, sto.name AS storage, sto.branch_office_id, bo.name AS branch_office, rms.status
                        FROM pur_raw_material_shipments AS rms
                        INNER JOIN wms_movements AS m
                        ON m.id = rms.movement_id
                        INNER JOIN pur_suppliers AS sup
                        ON sup.id = rms.supplier_id
                        INNER JOIN wms_storages AS sto
                        ON sto.id = m.storage_id
                        INNER JOIN wms_branch_offices AS bo
                        ON bo.id = sto.branch_office_id
                        WHERE rms.id = $id;";
                $shipment = $this->db->query($sql)->fetch();
                $this->content['result'] = true;
            }
            $this->content['shipment'] = $shipment;
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
                if (isset($request['supplierId']) && is_numeric($request['supplierId']) && isset($request['branchOfficeId']) && is_numeric($request['branchOfficeId'])) {
                    $storage = Storages::findFirst("storage_type_id = 9 AND branch_office_id = ".$request['branchOfficeId']);
                    if ($storage) {
                        $movement = new Movements();
                        $movement->setTransaction($tx);
                        $movement->storage_id = $storage->id;
                        $movement->type = 1;
                        if ($movement->create()) {
                            $shipment = new RawMaterialShipments();
                            $shipment->setTransaction($tx);
                            $shipment->movement_id = $movement->id;
                            $shipment->supplier_id = $request['supplierId'];
                            if ($shipment->create()) {
                                $this->content['result'] = true;
                                $this->content['shipment'] = $shipment;
                                $this->content['message'] = Message::success('La recepción de Materia Prima ha sido registrada correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($shipment);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la recepción.');
                                $tx->rollback();
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($movement);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el movimiento.');
                        }
                    } else {
                        $this->content['message'] = Message::error('La sucursal indicada no cuenta con almacén de materias primas.');
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

    public function execute ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $shipment = RawMaterialShipments::findFirst($id);

                if ($shipment) {
                    if ($shipment->status == 'NUEVO') {
                        $shipment->setTransaction($tx);
                        $shipment->status = 'EJECUTADO';

                        if ($shipment->update()) {
                            $movement = Movements::findFirst($shipment->movement_id);
                            if ($movement->status == 0) {
                                $movement->setTransaction($tx);
                                $movement->status = 1;
                                $movement->date = date('Y-m-d H:i:s');
                                if ($movement->update()) {
                                    $this->content['shipment'] = $shipment;
                                    $this->content['result'] = true;
                                    $this->content['message'] = Message::success('El estatus de la recepción ha cambiado a EJECUTADO.');
                                    $tx->commit();
                                } else {
                                    $this->content['error'] = Helpers::getErrors($movement);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el estatus de la recepción.');
                                    $tx->rollback();
                                }
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el estatus de la recepción.');
                                $tx->rollback();
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($shipment);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el estatus de la recepción.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se puede cambiar el estatus de la recepción.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la recepción indicada.');
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
