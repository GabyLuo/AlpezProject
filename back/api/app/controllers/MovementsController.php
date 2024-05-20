<?php

use Phalcon\Mvc\Controller;

class MovementsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function delete($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $movement = Movements::findFirst($id);
                $movement2 = Movements::findFirst("movement_id = $id");
                if ($movement) {
                    $movement->setTransaction($tx);

                    if ($movement->delete()) {
                        if ($movement2) {
                            $movement2->setTransaction($tx);
                            $movement2->delete();
                        }
                        $idIn = $id - 1;
                        $movement3 = Movements::findFirst("id = $idIn AND type_id = 5");
                        if ($movement3) {
                            $movement3->setTransaction($tx);
                            $movement3->delete();
                        }
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Movimiento ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($movement);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Movimiento.');
                        }
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Movimiento no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function update($id)
    {
        if (is_numeric($id)) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $movement = Movements::findFirst($id);

                if ($this->userHasPermission()) {
                    $movement->setTransaction($tx);
                    $movement->storage_id = $request['storage_id'];
                    $movement->date = $request['date'] . " " . date("h:i:s");
                    if (isset($request['po_id']) && is_numeric($request['po_id'])) {
                        $movement->po_id = $request['po_id'];
                    } else {
                        $movement->po_id = null;
                    }

                    if ($movement->update()) {
                        $this->content['result'] = true;
                        $this->content['movement'] = $movement;
                        $this->content['message'] = Message::success('El movimiento ha sido actualizado');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($movement);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar actualizar el movimiento');
                        $tx->rollback();
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No cuenta con los permisos necesarios');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        $this->response->setJsonContent($this->content);
    }
    public function create()
    {
        try {
            $canCreateMovement = true;
            $tx = $this->transactions->get();

            $request = $this->request->getPost();

            $movement = new Movements();
            $movement->setTransaction($tx);
            if (isset($request['storage_id']) && is_numeric($request['storage_id']) && isset($request['type_id']) && is_numeric($request['type_id'])) {
                $movement->storage_id = $request['storage_id'];
                $movement->folio = 0; // Es 0 porque se ocupa mandar algo para activar el trigger
                $movement->type_id = $request['type_id'];
                if (isset($request['po_id']) && is_numeric($request['po_id'])) {
                    $movement->po_id = $request['po_id'];
                } else {
                    $movement->po_id = null;
                }
                $movement->status = $request['status'];
                $movement->date = $request['date'] . " " . date("h:i:s");
                if ($canCreateMovement) {
                    if ($movement->create()) {
                        $this->content['result'] = true;
                        $this->content['movement_id'] = $movement->id;
                        $this->content['message'] = Message::success('El movimiento ha sido creado.');
                        if (isset($request['details']) && sizeof($request['details']) > 0) {
                            foreach ($request['details'] as $detail) {
                                $product = Products::findFirst($detail['product_id']);
                                $movementDetail = new MovementDetails();
                                $tx = $this->transactions->get();
                                $movementDetail->setTransaction($tx);
                                $movementDetail->movement_id = $movement->id;
                                $movementDetail->product_id = $product->id;
                                $movementDetail->bag_id = $detail['bag_id'];
                                $movementDetail->qty = $detail['qty'];
                                if (!$product->active || !$movementDetail->create()) {
                                    $this->content['error'] = Helpers::getErrors($movementDetail);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                    $tx->rollback();
                                }
                            }
                        }
                        if (isset($request['production_order_id']) && is_numeric($request['production_order_id'])) {
                            $productionOrder = ProductionOrders::findFirst($request['production_order_id']);
                            $productionOrder->setTransaction($tx);
                            $productionOrder->movement_id = $movement->id;
                            if (!$productionOrder->update()) {
                                $this->content['error'] = Helpers::getErrors($productionOrder);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar actualizar el movimiento de salida de la orden de produccion.');
                                $tx->rollback();
                            }
                        }
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($movement);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el movimiento.');
                        $tx->rollback();
                    }
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            $tx->rollback();
        }

        $this->response->setJsonContent($this->content);
    }
    public function cancel($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission2()) {
                    $tx = $this->transactions->get();

                    $mov = Movements::findFirst($id);

                    $request = $this->request->getPut();

                    if ($mov) {

                        $mov->setTransaction($tx);
                        $mov->status = 'CANCELADO';

                        if ($mov->update()) {

                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El movimiento a sido cancelado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($mov);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar cancelar el Movimiento.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el movimiento.');
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


    public function getFolioConsecutivo($id)
    {
        $id = intval($id);
        $year = date('Y');
        $sql = "SELECT max(folio) as folio FROM wms_movements WHERE type_id = $id and folio like '$year%'";
        $data = $this->db->query($sql)->fetchAll();
        if (sizeof($data) > 0) {
            $folio = $data[0]['folio'];
        } else {
            $folio = null;
        }

        if (is_numeric($folio)) {
            $new_folio = intval($folio) + 1;
        } else {
            $new_folio = date('Y') . '00001';
        }

        $this->content['folio'] = $new_folio;
        $this->content['result'] = 'success';
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function execute($movementId)
    {
        if (is_numeric($movementId)) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();

                $movement = Movements::findFirst($movementId);

                if ($this->userHasPermission()) {
                    if ($movement -> type_id == 3) {
                        $movement4 = Movements::findFirst("id = $movementId AND type_id = 3");
                        if ($movement4) {
                            $movementDetails = MovementDetails::find("movement_id = $movementId");
                            $merma = false;
                            $affected_product = [];
                            $storage_id = null;
                            foreach ($movementDetails as $key => $detail) {
                                //var_dump($detail->qty);
                                $storage_id = $detail->Movements->storage_id;
                                $inventoryStock = $this->generateStorageInventoryv4($detail->Movements->storage_id, $detail->product_id);
                                //var_dump($inventoryStock[0]['stock']);
                                $diff = intval($detail->qty) - intval($inventoryStock[0]['stock']);
                                $movementDetails[$key]->difference=abs($diff);
                                //$detail->qty=abs($diff);
                                //var_dump($diff);
                                if ($diff <= 0) {
                                    $merma = true;
                                    // var_dump($diff);
                                    array_push($affected_product, $movementDetails[$key]);
                                }
                            }
                            if ($merma) {
                                $movementMerma = new Movements();
                                //$tx = $this->transactions->get();
                                $movementMerma->setTransaction($tx);
                                $movementMerma->storage_id = $storage_id;
                                $movementMerma->folio = 0;
                                $movementMerma->type_id = 6;
                                $movementMerma->status = 'EJECUTADO';
                                $actualDate = date('Y-m-d', strtotime($movement->date.'-1 day'));
                                $movementMerma->date = $actualDate.' '.date("23:59:59");
                                if ($movementMerma ->create()) {
                                    foreach ($affected_product as $detail) {
                                        $movementDetail = new MovementDetails();
                                        //$tx = $this->transactions->get();
                                        $movementDetail->setTransaction($tx);
                                        $movementDetail->movement_id = $movementMerma->id;
                                        $movementDetail->product_id = $detail->product_id;
                                        $movementDetail->qty = $detail->difference;
                                        if (!$movementDetail->create()) {
                                            $this->content['error'] = Helpers::getErrors($movementDetail);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar guardar un detalle del movimiento.');
                                            $tx->rollback();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $movement->setTransaction($tx);
                    $movement->status = 'EJECUTADO';
                    $movement->ejecute_date = date('Y-m-d H:i:s');
                    if ($movement->update()) {
                        $movement2 = Movements::findFirst("movement_id = $movementId");
                        if ($movement2) {
                            $movement2->setTransaction($tx);
                            $movement2->status = 'EJECUTADO';
                            $movement2->ejecute_date = date('Y-m-d H:i:s');
                            $movement2->update();
                        }
                        $idIn = $movementId - 1;
                        $movement3 = Movements::findFirst("id = $idIn AND type_id = 5");
                        if ($movement3) {
                            $movement3->setTransaction($tx);
                            $movement3->status = 'EJECUTADO';
                            $movement3->ejecute_date = date('Y-m-d H:i:s');
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
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('No cuenta con los permisos necesarios');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        $this->response->setJsonContent($this->content);
    }
    public function execute_old($movementId)
    {
        if (is_numeric($movementId)) {
            try {
                $entryMovement = Movements::findFirst($movementId);
                $details = MovementDetails::find("movement_id = $entryMovement->id");
                $inactiveProducts = [];
                foreach ($details as $detail) {
                    $product = Products::findFirst($detail->product_id);
                    if (!$product->active) {
                        array_push($inactiveProducts, $product->name);
                    }
                }

                if (count($inactiveProducts) == 0) {
                    if ($entryMovement && $entryMovement->type == 1) {
                        if ($entryMovement->status == 1) {
                            $canExecute = false;
                            $this->content['message'] = Message::error('El movimiento ya ha sido ejecutado.');
                        } else {
                            $canExecute = true;
                            $sql = "SELECT id, bag_id
                                    FROM wms_movement_details AS md
                                    WHERE movement_id = $movementId;";
                            $details = $this->db->query($sql)->fetchAll();
                            foreach ($details as $detail) {
                                if (isset($detail['bag_id']) && !is_null($detail['bag_id'])) {
                                    $sql = "SELECT l.raw_material_movement_id, l.raw_material_return_movement_id, l.product_id, l.weight, l.order_id, md.bag_id
                                            FROM prd_lots AS l
                                            INNER JOIN wms_movements AS m
                                            ON m.id = l.raw_material_movement_id
                                            INNER JOIN wms_movement_details AS md
                                            ON md.movement_id = m.id
                                            WHERE m.status = 1
                                            AND md.bag_id = " . $detail['bag_id'] . "
                                            ORDER BY m.date DESC
                                            LIMIT 1;";
                                    $lastDetailExecuted = $this->db->query($sql)->fetch();
                                    if ($lastDetailExecuted) {
                                        if (isset($lastDetailExecuted['raw_material_return_movement_id']) && !is_null($lastDetailExecuted['raw_material_return_movement_id'])) {
                                            $sql = "SELECT md.id, md.movement_id, md.qty, md.bag_id, m.transaction_id
                                                    FROM wms_movements AS m
                                                    INNER JOIN wms_movement_details AS md
                                                    ON md.movement_id = m.id
                                                    WHERE m.id = " . $lastDetailExecuted['raw_material_return_movement_id'] . "
                                                    AND m.status = 1
                                                    AND md.bag_id = " . $lastDetailExecuted['bag_id'] . ";";
                                            $detailReturned = $this->db->query($sql)->fetch();
                                            if ($detailReturned) {
                                                $entryMovementDetail = MovementDetails::findFirst($detail['id']);
                                                if ($entryMovementDetail) {
                                                    $exitMovement = Movements::findFirst("transaction_id = $entryMovement->transaction_id AND type = 2");
                                                    if ($exitMovement) {
                                                        $exitMovementDetail = MovementDetails::findFirst("movement_id = $exitMovement->id AND bag_id = $entryMovementDetail->bag_id");
                                                        if ($exitMovementDetail) {
                                                            $exitMovementDetail->qty = $detailReturned['qty'];
                                                            if ($exitMovementDetail->update()) {
                                                                $entryMovementDetail->qty = $detailReturned['qty'];
                                                                if (!$entryMovementDetail->update()) {
                                                                    $canExecute = false;
                                                                    $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                                                }
                                                            } else {
                                                                $canExecute = false;
                                                                $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                                            }
                                                        } else {
                                                            $canExecute = false;
                                                            $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                                        }
                                                    } else {
                                                        $canExecute = false;
                                                        $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                                    }
                                                } else {
                                                    $canExecute = false;
                                                    $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                                }
                                            } else {
                                                $canExecute = false;
                                                $this->content['message'] = Message::error('El saco ' . $detail['bag_id'] . ' ya no se encuentra disponible, por favor seleccione otro.');
                                            }
                                        } else {
                                            $canExecute = false;
                                            $this->content['message'] = Message::error('El saco ' . $detail['bag_id'] . ' ya no se encuentra disponible, por favor seleccione otro.');
                                        }
                                    } else {
                                        $shipmentDetail = ShipmentDetails::findFirst($detail['bag_id']);
                                        $entryMovementDetail = MovementDetails::findFirst($detail['id']);
                                        if ($entryMovementDetail && $shipmentDetail) {
                                            $exitMovement = Movements::findFirst("transaction_id = $entryMovement->transaction_id AND type = 2");
                                            if ($exitMovement) {
                                                $exitMovementDetail = MovementDetails::findFirst("movement_id = $exitMovement->id AND bag_id = $entryMovementDetail->bag_id");
                                                if ($exitMovementDetail) {
                                                    $exitMovementDetail->qty = $shipmentDetail->qty;
                                                    if ($exitMovementDetail->update()) {
                                                        $entryMovementDetail->qty = $shipmentDetail->qty;
                                                        if (!$entryMovementDetail->update()) {
                                                            $canExecute = false;
                                                            $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                                        }
                                                    } else {
                                                        $canExecute = false;
                                                        $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                                    }
                                                } else {
                                                    $canExecute = false;
                                                    $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                                }
                                            } else {
                                                $canExecute = false;
                                                $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                            }
                                        } else {
                                            $canExecute = false;
                                            $this->content['message'] = Message::error('Error con el saco ' . $detail['bag_id'] . '.');
                                        }
                                    }
                                }
                            }
                            if ($canExecute) {
                                $tx = $this->transactions->get();
                                $exitMovement = Movements::findFirst("transaction_id = $entryMovement->transaction_id AND type = 2");
                                if ($exitMovement) {
                                    $exitMovement->status = 1;
                                    $exitMovement->date = date('Y-m-d H:i:s');
                                    if ($exitMovement->update()) {
                                        $entryMovement->status = 1;
                                        $entryMovement->date = date('Y-m-d H:i:s');
                                        if ($entryMovement->update()) {
                                            $lot = ProductionLots::findFirst("raw_material_movement_id = $entryMovement->id");
                                            if ($lot) {
                                                $lot->status = 'SURTIDO';
                                                if ($lot->update()) {
                                                    $previousStatusLot = ProductionLots::findFirst("id <> $lot->id AND order_id = $lot->order_id AND (status = 'NUEVO' OR status = 'FORMULADO')");
                                                    if (!$previousStatusLot) {
                                                        $order = ProductionOrders::findFirst($lot->order_id);
                                                        $order->status = 'SURTIDO';
                                                        if ($order->update()) {
                                                            $this->content['result'] = true;
                                                            $this->content['message'] = Message::success('El movimiento ha sido ejecutado exitosamente.');
                                                            $tx->commit();
                                                        } else {
                                                            $this->content['result'] = false;
                                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento.');
                                                            $tx->rollback();
                                                        }
                                                    } else {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('El movimiento ha sido ejecutado exitosamente.');
                                                        $tx->commit();
                                                    }
                                                } else {
                                                    $this->content['result'] = false;
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento.');
                                                    $tx->rollback();
                                                }
                                            } else {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('El movimiento ha sido ejecutado exitosamente.');
                                                $tx->commit();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($entryMovement);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento.');
                                            $tx->rollback();
                                        }
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($entryMovement);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento.');
                                        $tx->rollback();
                                    }
                                } else {
                                    $this->content['error'] = Helpers::getErrors($entryMovement);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento.');
                                    $tx->rollback();
                                }
                            }
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('Los siguientes productos se encuentra inactivos: ' . implode(', ', $inactiveProducts) . '.');
                }
            } catch (Exception $e) {
                $this->content['error'] = $e->getMessage();
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el movimiento.');
            }
        }

        $this->response->setJsonContent($this->content);
    }
    public function getMovements()
    {
        $validUser = Auth::getUserInfo($this->config);
        $where = "";
        $and = "";
        if ($validUser->role_id == 26) {
            $and = "INNER JOIN sys_supercluster on sys_supercluster.id = b.cluster_id and sys_supercluster.id = " . $validUser->cluster_id;
        } else {
            $where = $validUser->role_id == 1 ? '' : " and branch_office_id = $validUser->branch_office_id ";
        }
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT m.*, s.name as storage_name, s.branch_office_id as branch_id, b.name as branch_name, TO_CHAR(m.date, 'dd/mm/yyyy') as date
            FROM wms_movements AS m
            INNER JOIN wms_storages AS s
            ON m.storage_id = s.id AND m.status != 'ELIMINADO'
            INNER JOIN wms_branch_offices as b
            ON b.id = s.branch_office_id
            $and
            WHERE  m.date is not null
            $where
            ORDER BY m.date DESC, m.type_id DESC, m.folio DESC";
            $data = $this->db->query($sql);

            $content['movements'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getMovementOut($id)
    {
        $content = $this->content;
        $movement = null;

        $sqlType = "SELECT type_id, transaction_id FROM wms_movements WHERE id = $id";
        $dataType = $this->db->query($sqlType);
        $type = $dataType->fetch();

        if ($type['type_id'] == 4 || $type['type_id'] == 5) {
            $sqlTransaction = "SELECT id FROM wms_movements WHERE transaction_id = " . $type['transaction_id'] . " AND type_id = 5";
            $dataTransaction = $this->db->query($sqlTransaction);
            $transaction = $dataTransaction->fetch();

            $sqlTransactionTwo = "SELECT id FROM wms_movements WHERE transaction_id = " . $type['transaction_id'] . " AND type_id = 4";
            $dataTransactionTwo = $this->db->query($sqlTransactionTwo);
            $transactionTwo = $dataTransactionTwo->fetch();

            $sql = "SELECT m.id as id, m.storage_id, s.name AS storage,s.branch_office_id AS branch_id, b.name as branch, m.type_id, TO_CHAR(m.date :: DATE, 'dd/mm/yyyy') as date, m.status,m.folio,m.transaction_id
                FROM wms_movements AS m
                INNER JOIN wms_storages AS s
                ON m.storage_id = s.id
                INNER JOIN wms_branch_offices as b
                ON s.branch_office_id = b.id
                WHERE m.id = $id;";
            $data = $this->db->query($sql);
            $movement = $data->fetch();
            $movement['details'] = [];

            $sql = "SELECT md.id, p.code, md.movement_id, md.product_id, p.name AS product_name, md.unit_price as cost, md.qty as qty
                FROM wms_movement_details AS md
                INNER JOIN wms_products AS p
                ON md.product_id = p.id
                WHERE md.movement_id = " . $transaction['id'] . " OR md.movement_id = " . $transactionTwo['id'];
            $data = $this->db->query($sql);
            $movement['details'] = $data->fetchAll();
        } else {
            if (is_numeric($id)) {
                $sql = "SELECT m.id as id, m.storage_id, s.name AS storage,s.branch_office_id AS branch_id, b.name as branch, m.type_id, TO_CHAR(m.date :: DATE, 'dd/mm/yyyy') as date, m.status,m.folio,m.transaction_id
                        FROM wms_movements AS m
                        INNER JOIN wms_storages AS s
                        ON m.storage_id = s.id
                        INNER JOIN wms_branch_offices as b
                        ON s.branch_office_id = b.id
                        WHERE m.id = $id;";
                $data = $this->db->query($sql);
                $movement = $data->fetch();
                $movement['details'] = [];

                $sql = "SELECT md.id, concat(c.code,'-',l.code,'-',p.code) as code, md.movement_id, md.product_id, p.name AS product_name, md.unit_price as cost, md.qty as qty
                        FROM wms_movement_details AS md
                        INNER JOIN wms_products AS p
                        ON md.product_id = p.id
                        INNER JOIN wms_lines as l
                        ON l.id = p.line_id 
                        INNER JOIN wms_categories as c
                        ON c.id = l.category_id
                        WHERE md.movement_id = $id";
                $data = $this->db->query($sql);
                $movement['details'] = $data->fetchAll();

                if ($movement['type_id'] == 2 || $movement['type_id'] == 4 || $movement['type_id'] == 5) {
                    for ($i = 0; $i < count($movement['details']); $i++) {
                        $kardexCurrent = $this->getKardexMovement(null, null, 5, intval($movement['storage_id']), intval($movement['details'][$i]['product_id']));
                        //$movement['details'][$i]['current'] = $kardexCurrent[0]['stock'];
                    }
                }
            }
        }
        $content['movement'] = $movement;
        $content['result'] = true;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getKardexMovement($startDate, $endingDate, $branchOfficeId, $storageId, $productId)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $kardexAux = $this->generateKardex($startDate, $endingDate, $branchOfficeId, $storageId, null, null, $productId);
            $kardex = [];
            $existencia = 0;
            for ($i = 0; $i < sizeof($kardexAux); $i++) {
                $stock = 0;
                for ($j = 0; $j <= $i; $j++) {
                    if ($kardexAux[$j]['storage_id'] == $kardexAux[$i]['storage_id'] && $kardexAux[$j]['product_id'] == $kardexAux[$i]['product_id']) {
                        // Si el tipo de movimiento es 1 es ENTRADA por lo que se suma la cantidad, si es 2 es una SALIDA por lo que se resta
                        if ($kardexAux[$j]['movement_type'] == 1) {
                            $stock += $kardexAux[$j]['qty'];
                        } elseif ($kardexAux[$j]['movement_type'] == 2) {
                            $stock -= $kardexAux[$j]['qty'];
                        } elseif ($kardexAux[$j]['movement_type'] == 3) {
                            $stock = $kardexAux[$j]['qty'];
                        } elseif ($kardexAux[$j]['movement_type'] == 4) { //Entrada
                            $stock += $kardexAux[$j]['qty'];
                        } elseif ($kardexAux[$j]['movement_type'] == 5) { // Salida
                            $stock -= $kardexAux[$j]['qty'];
                            // Obtencion de la entrada ;                       
                        }
                    }
                }
                $date = new DateTime($kardexAux[$i]['date']);
                $kardexAux[$i]['date'] = $date->format('Y/m/d H:i');
                $kardexAux[$i]['stock'] = number_format($stock, 5);
                array_push($kardex, $kardexAux[$i]);
            }
            return array_reverse($kardex);
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getMovementEntry($id)
    {
        $content = $this->content;
        $movement = null;
        if (is_numeric($id)) {
            $sql = "SELECT m.id, m.storage_id, s.name AS storage,s.branch_office_id AS branch_id, b.name as branch, m.type_id, TO_CHAR(m.date :: DATE, 'dd/mm/yyyy') as date, m.status,m.folio
                    FROM wms_movements AS m
                    INNER JOIN wms_storages AS s
                    ON m.storage_id = s.id
                    INNER JOIN wms_branch_offices as b
                    ON s.branch_office_id = b.id
                    WHERE m.id = $id;";

            $data = $this->db->query($sql);
            $movement = $data->fetch();
            $movement['details'] = [];
            $sql = "SELECT md.id, md.movement_id, md.product_id, p.name AS product_name, md.unit_price as cost, md.qty as qty
                    FROM wms_movement_details AS md
                    INNER JOIN wms_products AS p
                    ON md.product_id = p.id
                    WHERE md.movement_id = $id;";
            $data = $this->db->query($sql);
            $movement['details'] = $data->fetchAll();
        }
        $content['movement'] = $movement;
        $content['result'] = true;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getKardex($startDate, $endingDate, $branchOfficeId, $storageId, $productId)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            // $kardexAux = $this->generateKardex($startDate, $endingDate, $branchOfficeId, $storageId, null, null, $productId);
            // $kardex = [];
            // $existencia = 0;
            // for ($i=0; $i < sizeof($kardexAux); $i++) {
            //     $stock = 0;
            //     for ($j=0; $j <= $i; $j++) {
            //         if ($kardexAux[$j]['storage_id'] == $kardexAux[$i]['storage_id'] && $kardexAux[$j]['product_id'] == $kardexAux[$i]['product_id']) {
            //             // Si el tipo de movimiento es 1 es ENTRADA por lo que se suma la cantidad, si es 2 es una SALIDA por lo que se resta
            //             if ($kardexAux[$j]['movement_type'] == 1) {
            //                 $stock += $kardexAux[$j]['qty'];
            //             } elseif ($kardexAux[$j]['movement_type'] == 2) {
            //                 $stock -= $kardexAux[$j]['qty'];
            //             }  elseif ($kardexAux[$j]['movement_type'] == 3) {
            //                 $stock = $kardexAux[$j]['qty'];
            //              }  elseif ($kardexAux[$j]['movement_type'] == 4) { //Entrada
            //                  $stock += $kardexAux[$j]['qty'];
            //             }  elseif ($kardexAux[$j]['movement_type'] == 5) { // Salida
            //                 $stock -= $kardexAux[$j]['qty'];
            //                 // Obtencion de la entrada ;                       
            //             }
            //         }
            //     }
            //     $date = new DateTime($kardexAux[$i]['date']);
            //     $kardexAux[$i]['date'] = $date->format('Y/m/d H:i');
            //     $kardexAux[$i]['stock'] = number_format($stock,3);
            //     array_push($kardex, $kardexAux[$i]);
            // }
            // $content['kardex'] = array_reverse($kardex);
            $startDate = $startDate != 'null' ? "'$startDate'" : "null";
            $endingDate = $endingDate != 'null' ? "'$endingDate'" : "null";
            $sql = "select * from getkardex($startDate,$endingDate,$branchOfficeId, $storageId,$productId) order by idx desc";
            $kardex = $this->db->query($sql)->fetchAll();

            $content['kardex'] = $kardex;
            $content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    public function getKardex_old($startDate, $endingDate, $branchOfficeId, $storageId, $productId, $bagId, $baleId)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $kardexAux = $this->generateKardex($startDate, $endingDate, $branchOfficeId, $storageId, null, null, $productId, $bagId, $baleId);
            $kardex = [];
            for ($i = 0; $i < sizeof($kardexAux); $i++) {
                $stock = 0;
                for ($j = 0; $j <= $i; $j++) {
                    if ($kardexAux[$j]['storage_id'] == $kardexAux[$i]['storage_id'] && $kardexAux[$j]['product_id'] == $kardexAux[$i]['product_id']) {
                        if ($kardexAux[$j]['movement_type'] == 1) {
                            $stock += $kardexAux[$j]['qty'];
                        } elseif ($kardexAux[$j]['movement_type'] == 2) {
                            $stock -= $kardexAux[$j]['qty'];
                        }
                    }
                }
                $date = new DateTime($kardexAux[$i]['date']);
                $kardexAux[$i]['date'] = $date->format('Y/m/d H:i');
                $kardexAux[$i]['stock'] = $stock;
                array_push($kardex, $kardexAux[$i]);
            }
            $content['kardex'] = array_reverse($kardex);
            $content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getCsvKardex($startDate, $endingDate, $branchOfficeId, $storageId, $productId)
    {
        $content = $this->content;
        $kardexAux = $this->generateKardex($startDate, $endingDate, $branchOfficeId, $storageId, null, null, $productId, null, null);
        $kardex = [];
        for ($i = 0; $i < sizeof($kardexAux); $i++) {
            $stock = 0;
            for ($j = 0; $j <= $i; $j++) {
                if ($kardexAux[$j]['storage_id'] == $kardexAux[$i]['storage_id'] && $kardexAux[$j]['product_id'] == $kardexAux[$i]['product_id']) {
                    // Si el tipo de movimiento es 1 es ENTRADA por lo que se suma la cantidad, si es 2 es una SALIDA por lo que se resta
                    if ($kardexAux[$j]['movement_type'] == 1) {
                        $stock += $kardexAux[$j]['qty'];
                    } elseif ($kardexAux[$j]['movement_type'] == 2) {
                        $stock -= $kardexAux[$j]['qty'];
                    } elseif ($kardexAux[$j]['movement_type'] == 3) {
                        $stock = $kardexAux[$j]['qty'];
                    } elseif ($kardexAux[$j]['movement_type'] == 4) { //Entrada
                        $stock += $kardexAux[$j]['qty'];
                    } elseif ($kardexAux[$j]['movement_type'] == 5) { // Salida
                        $stock -= $kardexAux[$j]['qty'];
                        // Obtencion de la entrada ;                       
                    } elseif ($kardexAux[$j]['movement_type'] == 6) {
                        $stock -= $kardexAux[$j]['qty'];
                    }
                }
            }
            $date = new DateTime($kardexAux[$i]['date']);
            $kardexAux[$i]['date'] = $date->format('Y/m/d H:i');
            $kardexAux[$i]['stock'] = $stock;
            array_push($kardex, $kardexAux[$i]);
        }
        $kardex = array_reverse($kardex);

        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['FECHA', 'TIPO', utf8_decode('ESTACIÓN'), utf8_decode('ALMACÉN'), 'PRODUCTO', 'CANTIDAD', 'SALDO'], ',');

        if (count($kardex) > 0) {
            foreach ($kardex  as $kdx) {
                fputcsv($fp, [
                    $kdx['date'],
                    utf8_decode(($kdx['movement_type'] == 1 ? 'ENTRADA' : ($kdx['movement_type'] == 2 ? 'SALIDA' : ($kdx['movement_type'] == 3 ? 'INVENTARIO FÍSICO' : ($kdx['movement_type'] == 6 ? 'MERMA' : ($kdx['movement_type'] == 5 ? 'TRASPASO (SALIDA)' : 'TRASPASO (ENTRADA)')))))),
                    utf8_decode($kdx['branch_office_name']),
                    utf8_decode($kdx['storage_name']),
                    utf8_decode($kdx['category_code'] . '-' . $kdx['line_code'] . '-' . $kdx['product_name']),
                    number_format($kdx['qty'], 2, '.', ',') . ' pza.',
                    number_format($kdx['stock'], 2, '.', ',') . ' pza.'
                ], ',');
            }
            $content['result'] = 'success';
        }

        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader("Access-Control-Allow-Origin", "*");
        $this->response->setHeader("Access-Control-Allow-Headers", "*");
        $this->response->setHeader('Content-Disposition', 'attachment; filename=Kardex-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getInventoryPdf($user, $branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date)
    {

        $data = $this->generateStorageInventoryv3($branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date, $user);
        $widths = array(8, 50, 65, 115, 25, 20);
        $aligns = array('C', 'L', 'L', 'l', 'R');

        $pdf = new PDFInventory();
        $pdf->encabezadoDriver('Existencias', $branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date);
        $pdf->SetTextColor(0);
        $pdf->Ln();
        $pdf->SetXY(8, 57);
        $pdf->SetFont('Arial', null, 8);
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        $fill = false;
        $y = 32;
        $i = 1;
        foreach ($data as $kdx) {
            $pdf->SetX(8);
            $pdf->Row(array($i, utf8_decode($kdx['category_name']), utf8_decode($kdx['line_name']), $kdx['product_name'], number_format($kdx['stock'], 3, '.', ',')), $fill);
            $y += 6;
            $i++;
        }

        $pdf->Output('I', 'existencias.pdf', true);
        $response = new Phalcon\Http\Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', '*');
        $response->setHeader('Content-Disposition', 'inline; filename=existencias.pdf');
        return $response;
    }

    public function getInventoryMinimalStockPdf($branchOfficeId, $storageId, $category, $line, $product, $user, $mark)
    {
        $where = 'WHERE p.id > 0 ';
        if ($category == 'TODOS') {
            $where .= " AND (l.category_id = {$category} OR {$category} IS NULL)";
        } else if ($category == '') {
        } else {
            $where .= " AND (l.category_id = {$category} OR {$category} IS NULL)";
        }
        if ($line == 'TODOS') {
            $where .= " AND (l.id = {$line} OR {$line} IS NULL)";
        } else if ($line == '') {
        } else {
            $where .= " AND (l.id = {$line} OR {$line} IS NULL)";
        }
        if ($branchOfficeId == 'TODOS') {
            "AND (bo.id = {$branchOfficeId} OR {$branchOfficeId} IS NULL)";
        } else if ($branchOfficeId == '') {
        } else {
            $where .= "AND (bo.id = {$branchOfficeId} OR {$branchOfficeId} IS NULL)";
        }
        if ($storageId == 'TODOS') {
            "AND (ps.storage_id = {$storageId} OR {$storageId} IS NULL)";
        } else if ($storageId == '') {
        } else {
            $where .= " AND (ps.storage_id = {$storageId} OR {$storageId} IS NULL)";
        }
        if ($product == 'TODOS') {
            $where .= " AND (ps.product_id = {$product} OR {$product} IS NULL)";
        } else if ($product == '') {
        } else {
            $where .= " AND (ps.product_id = {$product} OR {$product} IS NULL)";
        }
        if ($mark == 'TODOS') {
        } else if ($mark == '') {
        } else {
            $where .= " AND (ma.id = {$mark} OR {$mark} IS NULL)";
        }
        $sortBy = "";
        $sql = "SELECT count(p.id) AS count
                FROM v_product_stock_price AS ps
                LEFT JOIN wms_products as p on p.id = ps.product_id
                INNER JOIN wms_lines AS l 
                ON p.line_id = l.id
                INNER JOIN wms_categories AS c 
                ON l.category_id = c.id
                LEFT JOIN wms_products_minimum_stock AS pm
                ON p.id = pm.product_id and ps.storage_id = pm.storage_id
                LEFT JOIN wms_branch_offices AS bo
                ON bo.id = pm.branch_offices_id
                LEFT JOIN wms_storages AS s 
                ON s.id = pm.storage_id
                INNER JOIN wms_marks AS ma
                ON p.mark_id = ma.id
            {$where}
            group by l.category_id, c.code, c.name,ps.product_id,s.id,bo.id,bo.name,pm.stock,p.id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name
";
        // print_r($sql);
        ///exit();
        $productsCount = $this->db->query($sql)->fetchAll();
        $sql = "SELECT p.id, p.line_id, CONCAT(c.code,'-',l.code,'-',p.code) as old_code, CONCAT(c.code,'-',l.code,'-',p.code) as code, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-') AS product_codes, p.name as product_name, p.photo, p.active, p.clave_producto_id,p.description,p.mark_id,rebasa_code,ma.name as marca,
        c.code as category_code,l.code as line_code,p.code as product_code, pm.stock as minimal_stock,bo.name as branch_office_name, s.name as storage_name,0 as stock,bo.id as branch_office_id,ps.storage_id as storage_id
                FROM v_product_stock_price AS ps
                LEFT JOIN wms_products as p on p.id = ps.product_id
                LEFT JOIN wms_lines AS l 
                ON p.line_id = l.id
                LEFT JOIN wms_categories AS c 
                ON l.category_id = c.id
                LEFT JOIN wms_products_minimum_stock AS pm
                ON p.id = pm.product_id and ps.storage_id = pm.storage_id
                LEFT JOIN wms_storages AS s 
                ON s.id = ps.storage_id
                LEFT JOIN wms_branch_offices AS bo
                ON bo.id = s.branch_office_id
                LEFT JOIN wms_marks AS ma
                ON p.mark_id = ma.id
                {$where} 
                group by l.category_id, c.code, c.name,ps.product_id,s.id,bo.id,bo.name,pm.stock,p.id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name,ps.storage_id

                {$sortBy}    ;";

        $dataaux = $this->db->query($sql)->fetchAll();
        //print_r($sql);
        // exit();
        for ($i = 0; $i < count($dataaux); $i++) {
            if ($dataaux[$i]['storage_id'] != null) {
                $stock = $this->generateStorageInventoryv3(null, $dataaux[$i]['storage_id'], $dataaux[$i]['category_id'], $dataaux[$i]['line_id'], $dataaux[$i]['id'], null, null, $user);
                //$stock = $this->generateStorageInventoryv3 (null, $dataaux[$i]['storage_id'],$dataaux[$i]['category_id'], $dataaux[$i]['line_id'], $dataaux[$i]['id'], null, null, $user);
                // $dataaux = $dataaux['data'];
                if ($stock) {
                    $dataaux[$i]['stock'] = $stock['data'][0]['stock'];
                    // print_r($stock['data'][0]['stock']);
                }
            }
        }

        $widths = array(8, 35, 35, 35, 35, 25, 20, 25, 20, 20);
        $aligns = array('C', 'L', 'L', 'l', 'L', 'L', 'L', 'l', 'R', 'R');
        $pdf = new PDFInventorystockMinimal();
        $pdf->encabezadoDriverMinimal('Stock Minimo', $branchOfficeId, $storageId, $category, $line, $product);
        $pdf->SetTextColor(0);
        $pdf->Ln();
        $pdf->SetXY(8, 57);
        $pdf->SetFont('Arial', null, 8);
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        $fill = false;
        $y = 32;
        $i = 1;
        foreach ($dataaux as $kdx) {
            $pdf->SetX(8);
            $pdf->Row(array($i, utf8_decode($kdx['category']), utf8_decode($kdx['line']), $kdx['code'], $kdx['branch_office_name'], $kdx['storage_name'], $kdx['marca'], $kdx['product_name'], number_format($kdx['minimal_stock'], 3, '.', ','), number_format($kdx['stock'], 3, '.', ',')), $fill);
            $y += 6;
            $i++;
        }
        // exit();
        $pdf->Output('I', 'minimal_stock.pdf', true);
        $response = new Phalcon\Http\Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', '*');
        $response->setHeader('Content-Disposition', 'inline; filename=existencias.pdf');
        return $response;
    }

    public function getInventoryMinimalStockCsv($branchOfficeId, $storageId, $category, $line, $product, $user, $mark)
    {
        $where = 'WHERE p.id > 0 ';
        if ($category == 'TODOS') {
            $where .= " AND (l.category_id = {$category} OR {$category} IS NULL)";
        } else if ($category == '') {
        } else {
            $where .= " AND (l.category_id = {$category} OR {$category} IS NULL)";
        }
        if ($line == 'TODOS') {
            $where .= " AND (l.id = {$line} OR {$line} IS NULL)";
        } else if ($line == '') {
        } else {
            $where .= " AND (l.id = {$line} OR {$line} IS NULL)";
        }
        if ($branchOfficeId == 'TODOS') {
            "AND (bo.id = {$branchOfficeId} OR {$branchOfficeId} IS NULL)";
        } else if ($branchOfficeId == '') {
        } else {
            $where .= "AND (bo.id = {$branchOfficeId} OR {$branchOfficeId} IS NULL)";
        }
        if ($storageId == 'TODOS') {
            "AND (ps.storage_id = {$storageId} OR {$storageId} IS NULL)";
        } else if ($storageId == '') {
        } else {
            $where .= " AND (ps.storage_id = {$storageId} OR {$storageId} IS NULL)";
        }
        if ($product == 'TODOS') {
            $where .= " AND (ps.product_id = {$product} OR {$product} IS NULL)";
        } else if ($product == '') {
        } else {
            $where .= " AND (ps.product_id = {$product} OR {$product} IS NULL)";
        }
        if ($mark == 'TODOS') {
        } else if ($mark == '') {
        } else {
            $where .= " AND (ma.id = {$mark} OR {$mark} IS NULL)";
        }
        $sortBy = "";
        $sql = "SELECT count(p.id) AS count
                FROM v_product_stock_price AS ps
                LEFT JOIN wms_products as p on p.id = ps.product_id
                INNER JOIN wms_lines AS l 
                ON p.line_id = l.id
                INNER JOIN wms_categories AS c 
                ON l.category_id = c.id
                LEFT JOIN wms_products_minimum_stock AS pm
                ON p.id = pm.product_id and ps.storage_id = pm.storage_id
                LEFT JOIN wms_branch_offices AS bo
                ON bo.id = pm.branch_offices_id
                LEFT JOIN wms_storages AS s 
                ON s.id = pm.storage_id
                INNER JOIN wms_marks AS ma
                ON p.mark_id = ma.id
            {$where}
            group by l.category_id, c.code, c.name,ps.product_id,s.id,bo.id,bo.name,pm.stock,p.id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name
";
        // print_r($sql);
        ///exit();
        $productsCount = $this->db->query($sql)->fetchAll();
        $sql = "SELECT p.id, p.line_id, CONCAT(c.code,'-',l.code,'-',p.code) as old_code, CONCAT(c.code,'-',l.code,'-',p.code) as code, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-') AS product_codes, p.name as product_name, p.photo, p.active, p.clave_producto_id,p.description,p.mark_id,rebasa_code,ma.name as marca,
        c.code as category_code,l.code as line_code,p.code as product_code, pm.stock as minimal_stock,bo.name as branch_office_name, s.name as storage_name,0 as stock,bo.id as branch_office_id,ps.storage_id as storage_id
                FROM v_product_stock_price AS ps
                LEFT JOIN wms_products as p on p.id = ps.product_id
                LEFT JOIN wms_lines AS l 
                ON p.line_id = l.id
                LEFT JOIN wms_categories AS c 
                ON l.category_id = c.id
                LEFT JOIN wms_products_minimum_stock AS pm
                ON p.id = pm.product_id and ps.storage_id = pm.storage_id
                LEFT JOIN wms_storages AS s 
                ON s.id = ps.storage_id
                LEFT JOIN wms_branch_offices AS bo
                ON bo.id = s.branch_office_id
                LEFT JOIN wms_marks AS ma
                ON p.mark_id = ma.id
                {$where} 
                group by l.category_id, c.code, c.name,ps.product_id,s.id,bo.id,bo.name,pm.stock,p.id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name,ps.storage_id

                {$sortBy}    ;";

        $dataaux = $this->db->query($sql)->fetchAll();
        //print_r($sql);
        // exit();
        for ($i = 0; $i < count($dataaux); $i++) {
            if ($dataaux[$i]['storage_id'] != null) {
                $stock = $this->generateStorageInventoryv3(null, $dataaux[$i]['storage_id'], $dataaux[$i]['category_id'], $dataaux[$i]['line_id'], $dataaux[$i]['id'], null, null, $user);
                //$stock = $this->generateStorageInventoryv3 (null, $dataaux[$i]['storage_id'],$dataaux[$i]['category_id'], $dataaux[$i]['line_id'], $dataaux[$i]['id'], null, null, $user);
                // $dataaux = $dataaux['data'];
                if ($stock) {
                    $dataaux[$i]['stock'] = $stock['data'][0]['stock'];
                    // print_r($stock['data'][0]['stock']);
                }
            }
        }
        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['#', utf8_decode('CATEGORÍA'), utf8_decode('LÍNEA'), utf8_decode('CÓDIGO
            '), utf8_decode('SUCURSAL'), utf8_decode('ALMACÉN'), utf8_decode('MARCA'), utf8_decode('PRODUCTO'), utf8_decode('MINIMO'), utf8_decode('EXISTENCIA')], ',');
        // $y = 32;
        $i = 1;
        if (count($dataaux) > 0) {
            foreach ($dataaux  as $kdx) {
                fputcsv($fp, [
                    $i,
                    utf8_decode($kdx['category']),
                    utf8_decode($kdx['line']),
                    utf8_decode($kdx['code']),
                    $kdx['branch_office_name'],
                    $kdx['storage_name'],
                    $kdx['marca'],
                    $kdx['product_name'],
                    number_format($kdx['minimal_stock'], 3, '.', ','),
                    number_format($kdx['stock'], 3, '.', ',')
                ], ',');
                // $y += 6;
                $i++;
            }
            $content['result'] = 'success';
        }

        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader("Access-Control-Allow-Origin", "*");
        $this->response->setHeader("Access-Control-Allow-Headers", "*");
        $this->response->setHeader('Content-Disposition', 'attachment; filename=stock-minimo-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }

    public function getInventorybMSPdf($branchOfficeId, $storageId, $category, $line, $product, $user, $mark)
    {
        $dataaux = $this->generateStorageInventoryv3($branchOfficeId != 'null' ? $branchOfficeId : '', $storageId != 'null' ? $storageId : '', $category != 'null' ? $category : '', $line != 'null' ? $line : '', $product != 'null' ? $product : '', $mark != 'null' ? $mark : '', null, $user);
        $dataaux = $dataaux['data'];
        $widths = array(8, 25, 35, 55, 35, 35, 25, 25, 25, 25);
        $aligns = array('C', 'L', 'L', 'l', 'L', 'L', '', 'R', 'R');
        $pdf = new PDFInventory();
        $pdf->encabezadoDriver('Existencia', $branchOfficeId, $storageId, $category, $line, $product, null);
        $pdf->SetTextColor(0);
        $pdf->Ln();
        $pdf->SetXY(8, 57);
        $pdf->SetFont('Arial', null, 8);
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        $fill = false;
        $y = 32;
        $i = 1;
        foreach ($dataaux as $kdx) {
            $pdf->SetX(8);
            $pdf->Row(array(
                $i, utf8_decode($kdx['category_name']), utf8_decode($kdx['line_name']),
                $kdx['category_code'] . '-' . $kdx['line_code'] . '-' . $kdx['product_code'], $kdx['product_name'], $kdx['marca'], $kdx['almacen'], number_format($kdx['price'], 2), number_format($kdx['stock'], 3, '.', ',')
            ), $fill);
            $y += 6;
            $i++;
        }
        // exit();
        $pdf->Output('I', 'minimal_stock.pdf', true);
        $response = new Phalcon\Http\Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', '*');
        $response->setHeader('Content-Disposition', 'inline; filename=existencias.pdf');
        return $response;
    }


    public function getInventorybMSCsv($branchOfficeId, $storageId, $category, $line, $product, $user, $mark)
    {
        $dataaux = $this->generateStorageInventoryv3($branchOfficeId != 'null' ? $branchOfficeId : '', $storageId != 'null' ? $storageId : '', $category != 'null' ? $category : '', $line != 'null' ? $line : '', $product != 'null' ? $product : '', $mark != 'null' ? $mark : '', null, $user);
        $dataaux = $dataaux['data'];

        $fp = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($fp, ['#', utf8_decode('CATEGORÍA'), utf8_decode('LÍNEA'), utf8_decode('CÓDIGO
            '), utf8_decode('PRODUCTO'), utf8_decode('MARCA'), utf8_decode('ALMACEN'), utf8_decode('PRECIO'), utf8_decode('EXISTENCIA')], ',');
        // $y = 32;
        $i = 1;
        if (count($dataaux) > 0) {
            foreach ($dataaux  as $kdx) {
                fputcsv($fp, [
                    $i,
                    utf8_decode($kdx['category_name']),
                    utf8_decode($kdx['line_name']),
                    utf8_decode($kdx['category_code'] . '-' . $kdx['line_code'] . '-' . $kdx['product_code']),
                    $kdx['product_name'],
                    $kdx['marca'],
                    $kdx['almacen'],
                    number_format($kdx['price'], 2),
                    number_format($kdx['stock'], 3, '.', ',')
                ], ',');
                // $y += 6;
                $i++;
            }
            $content['result'] = 'success';
        }

        rewind($fp);
        $output = stream_get_contents($fp);
        mb_convert_encoding($output, 'UCS-2LE', 'UTF-8');
        fclose($fp);

        $this->response->resetHeaders();
        $this->response->setHeader('Content-Type', 'application/csv');
        $this->response->setHeader("Access-Control-Allow-Origin", "*");
        $this->response->setHeader("Access-Control-Allow-Headers", "*");
        $this->response->setHeader('Content-Disposition', 'attachment; filename=existencias-' . date('Y-m-d') . '.csv');
        $this->response->setContent($output);
        $this->response->send();
    }


    private function getStock($startDate, $endingDate, $branchOfficeId, $storageId, $product_id)
    {
        $dateStart = 'null';
        $dateEnd = 'null';
        $branch = 'null';
        $storage = 'null';

        if ($startDate != 'null') {
            $dateStart = "'" . $startDate . "'";
        }
        if ($endingDate != 'null') {
            $dateEnd = "'" . $endingDate . "'";
        }
        if ($branchOfficeId != 'null') {
            $branch = $branchOfficeId;
        }
        if ($storageId != 'null') {
            $storage = $storageId;
        }
        $sql = "SELECT *, wp.name from getkardex($dateStart, $dateEnd, $branch, $storage, $product_id)
             Inner join wms_products as wp on wp.id = $product_id order by idx desc";
        $data = $this->db->query($sql)->fetchAll();
        // var_dump($data);

        return $data;
    }

    public function getPdfKardex($startDate, $endingDate, $branchOfficeId, $storageId, $product_id)
    {
        $content = $this->content;
        $stocks = $this->getStock($startDate, $endingDate, $branchOfficeId, $storageId, $product_id);
        // var_dump($stocks);

        $content['result'] = true;

        $pdf = new PDFKardex('L', 'mm', 'Letter');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->SetFont('Arial', '', 10);

        $pdf->AddPage();
        $pdf->SetY($pdf->GetY() + 5);
        $pdf->Cell(80, 6, utf8_decode("PRODUCTO: " . $stocks[0]['name']), 0, 1, 'L');


        $pdf->Ln();
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetWidths(array(22, 45, 25, 28, 77, 31, 31));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $pdf->SetHeight(6);
        $pdf->SetDrawEdge(false);
        $pdf->SetFill(array(true, true, true, true, true, true, true));
        //30,136,229
        $pdf->SetFillColor(128, 179, 240);
        $pdf->Row(array(utf8_decode('FECHA'), utf8_decode('TIPO'), utf8_decode('ESTACIÓN'), utf8_decode('ALMACÉN'), utf8_decode('PRODUCTO'), utf8_decode('CANTIDAD'), utf8_decode('SALDO')));
        $pdf->SetAligns(array('C', 'L', 'L', 'L', 'L', 'R', 'R'));
        $fill = false;
        $totalWeight = 0;

        foreach ($stocks as $d) {
            // var_dump($row);
            //135, 180, 223 
            $pdf->SetFillColor(200, 220, 240);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFill(array($fill, $fill, $fill, $fill, $fill, $fill, $fill, $fill));
            $pdf->Row(array(
                utf8_decode($d['date']), utf8_decode(($d['type_id'] == 1 ? 'ENTRADA' : ($d['type_id'] == 2 ? 'SALIDA' : ($d['type_id'] == 3 ? 'INVENTARIO FÍSICO' : ($d['type_id'] == 6 ? 'MERMA' : ($d['type_id'] == 5 ? 'TRASPASO (SALIDA)' : 'TRASPASO (ENTRADA)')))))),
                utf8_decode($d['branch_office_name']),
                utf8_decode($d['storage_name']),
                utf8_decode($d['category_code'] . '-' . $d['line_code'] . '-' . $d['name']),
                number_format($d['qty'], 2, '.', ',') . ' pza.',
                number_format($d['stock'], 2, '.', ',') . ' pza.'
            ));
            $fill = !$fill;
        }

        $pdf->SetTitle("Kardex", true);
        $pdf->Output('I', "Kardex", true);
        //Agregué esto
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        $response = new Phalcon\Http\Response();
        /*$response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', '*'); */

        $response->setHeader("Content-Type", "application/pdf");
        $response->setHeader("Content-Disposition", 'inline; filename="Kardex"');

        return $pdf;
    }

    public function getStorageInventory($branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $stock = $this->generateStorageInventory($branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date);
            $content['stock'] = $stock;
            $content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getStorageInventoryv2($branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $stock = $this->generateStorageInventoryv3($branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date);
            $content['stock'] = $stock;
            $content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    //
    public function getStorageInventoryMinimal()
    {
        $request = $this->request->getPost();
        $content = $this->content;
        // print_r($request);
        // exit();
        if ($this->userHasPermission()) {
            $response = $this->getGridSQL($request['branchOfficeId'], $request['storageId'], $request['categoryId'], $request['lineId'], $request, $request['product'], $request['mark']);
            $content['stock'] = $response;
            $content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getStorageInventoryByMark()
    {
        $request = $this->request->getPost();
        $content = $this->content;
        // print_r($request);
        // exit();
        if ($this->userHasPermission()) {
            //$response = $this->getGridSQLbyMark($request['branchOfficeId'], $request['storageId'], $request['categoryId'], $request['lineId'],$request,$request['product'],$request['mark']);
            $response = $this->generateStorageInventoryv3($request['branchOfficeId'], $request['storageId'], $request['categoryId'], $request['lineId'], $request['product'], $request['mark'], $request);
            $content['stock'] = $response;
            $content['result'] = true;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    //
    // null, $date, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, null, null
    public function generateMultiKardex($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts)
    {
        $productIds = [];
        if ($detailProducts) {
            foreach ($detailProducts as $detail) {
                // $productIds[] =  $detail['product_id'];
                $productIds[] =  $detail->product_id;
            }
        }
        $productIds = implode(',', $productIds);

        $sql = "SELECT m.type_id AS movement_type, m.date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, md.unit_price, u.nickname AS creator
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
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
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
        if (!empty($productIds)) {
            $sql .= " AND md.product_id in ($productIds)";
        }

        // $sql .= " ORDER BY m.date ASC, m.type_id DESC;";

        $data = $this->db->query($sql);
        return $data->fetchAll();
    }
    public function generateMultiKardex_old($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, $bagId, $detailBales)
    {
        $baleIds = [];
        $productIds = [];
        if ($detailBales) {
            foreach ($detailBales as $detail) {
                $baleIds[] = $detail->bale_id;
            }
        }
        if ($detailProducts) {
            foreach ($detailProducts as $detail) {
                // $productIds[] =  $detail['product_id'];
                $productIds[] =  $detail->product_id;
            }
        }
        $baleIds = implode(',', $baleIds);
        $productIds = implode(',', $productIds);

        $sql = "SELECT m.type AS movement_type, m.date, md.bag_id, md.bale_id, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, md.unit_price, u.nickname AS creator
                FROM wms_movement_details AS md
                JOIN wms_movements AS m ON m.id = md.movement_id
                JOIN wms_storages AS s ON s.id = m.storage_id 
                JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
                JOIN wms_products AS p ON p.id = md.product_id
                JOIN wms_lines AS l ON l.id = p.line_id
                JOIN wms_categories AS c ON c.id = l.category_id
                JOIN sys_users AS u ON u.id = m.created_by
                WHERE m.status = 1";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
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
        if (!empty($productIds)) {
            $sql .= " AND md.product_id in ($productIds)";
        }
        if (!is_null($bagId) && is_numeric($bagId)) {
            $sql .= " AND md.bag_id = $bagId";
        }
        if (!empty($baleIds)) {
            $sql .= " AND md.bale_id in ($baleIds)";
        }

        $sql .= " ORDER BY m.date ASC, m.type DESC;";

        $data = $this->db->query($sql);
        return $data->fetchAll();
    }

    public function generateKardex($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId)
    {
        $validUser = Auth::getUserInfo($this->config);
        $where = "";
        $and = "";
        if ($validUser->role_id == 26) {
            $and = "INNER JOIN sys_supercluster on sys_supercluster.id = bo.cluster_id and sys_supercluster.id = " . $validUser->cluster_id;
        } else {
            $where = $validUser->role_id == 1 ? '' : " and s.branch_office_id = $validUser->branch_office_id ";
        }

        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id $and
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' $where";
        $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 WHEN 6 then 6 else 3 END";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 1 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 WHEN 6 then 6 else 3 END";
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

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' $where ";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
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

    public function generateKardexBymark($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId, $user)
    {
        $where = "";
        if ($user > 0) {
            $validUser = Users::findFirst($user);
            $bo = $validUser['branch_office_id'];

            $where = $validUser['role_id'] == 1 ? '' : " and s.branch_office_id = $bo ";
        }
        // $validUser = Auth::getUserInfo($this->config);
        // $where = $validUser->role_id == 1 ? '' : " and s.branch_office_id = $validUser->branch_office_id ";

        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' $where";
        $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
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

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' $where ";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
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

    public function generateKardexMinimal($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId)
    {

        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1 ? '' : " and s.branch_office_id = $validUser->branch_office_id ";


        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code,bo.id as sucursal_id
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' $where";
        $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
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

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code,bo.id as sucursal_id
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' $where ";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
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
    public function generateKardex_old($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId, $bagId, $baleId)
    {
        $sql = "SELECT md.id as mdid, m.type AS movement_type,p.active as status_product, m.date as date, md.bag_id, md.bale_id, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty, md.unit_price, u.nickname AS creator
                FROM wms_movement_details AS md
                JOIN wms_movements AS m ON m.id = md.movement_id
                JOIN wms_storages AS s ON s.id = m.storage_id 
                JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
                JOIN wms_products AS p ON p.id = md.product_id
                JOIN wms_lines AS l ON l.id = p.line_id
                JOIN wms_categories AS c ON c.id = l.category_id
                JOIN sys_users AS u ON u.id = m.created_by
                WHERE m.status = 1";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
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
        if (!is_null($bagId) && is_numeric($bagId)) {
            $sql .= " AND md.bag_id = $bagId";
        }
        if (!is_null($baleId) && is_numeric($baleId)) {
            $sql .= " AND md.bale_id = $baleId";
        }

        $sql .= " ORDER BY m.date ASC, m.type DESC;";

        $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $data->fetchAll();
    }


    public function generateKardexMinimalPdf($startDate, $endingDate, $branchOfficeId, $storageId, $categoryId, $lineId, $productId, $user)
    {
        // findFirst
        $where = "";
        if ($user > 0) {
            $validUser = Users::findFirst($user);
            $bo = $validUser['branch_office_id'];

            $where = $validUser['role_id'] == 1 ? '' : " and s.branch_office_id = $bo ";
        }

        //$validUser = Auth::getUserInfo($this->config);
        // $where = $validUser->role_id == 1 ? '' : " and s.branch_office_id = $validUser->branch_office_id ";


        $sql = "SELECT * FROM (SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code,bo.id as sucursal_id
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.movement_id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' $where";
        $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
        }
        if (!is_null($branchOfficeId) && is_numeric($branchOfficeId)) {
            $sql .= " AND s.branch_office_id = $branchOfficeId";
        }
        if (!is_null($storageId) && is_numeric($storageId)) {
            $sql .= " AND m.storage_id = $storageId";
            $caseOrder = "CASE movement_type WHEN 3 then 6 WHEN 1 then 2 WHEN 4 then 3 WHEN 2 then 4 WHEN 5 then 5 else 3 END";
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

        $sql .= "UNION ALL SELECT md.id as mdid, m.folio AS foli, m.type_id AS movement_type, p.active as status_product, TO_CHAR(m.date :: DATE, 'yyyy/mm/dd') as date, s.branch_office_id, bo.name AS branch_office_name, m.storage_id, s.name AS storage_name, l.category_id, c.code AS category_code, c.name AS category_name, p.line_id, l.code AS line_code, l.name AS line_name, md.product_id, p.code AS product_code, p.name AS product_name, md.qty AS qty, TRUNC((md.unit_price)::numeric,5) as unit_price, u.nickname AS creator, p.old_code as old_code,bo.id as sucursal_id
        FROM wms_movement_details AS md
        JOIN wms_movements AS m ON m.id = md.movement_id
        JOIN wms_storages AS s ON s.id = m.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = md.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        JOIN sys_users AS u ON u.id = m.created_by
        WHERE m.status = 'EJECUTADO' $where ";
        if (!is_null($startDate) && (date('Y-m-d', strtotime($startDate)) == $startDate)) {
            $sDate = date('Y-m-d', strtotime($startDate));
            $sql .= " AND m.date >= '" . $sDate . "'";
        }
        if (!is_null($endingDate) && (date('Y-m-d', strtotime($endingDate)) == $endingDate)) {
            $eDate = date('Y-m-d', strtotime($endingDate . "+ 1 days"));
            $sql .= " AND m.date <= '" . $eDate . "'";
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



    public function getStockProducts($storageId, $detailProducts)
    {

        $productIds = [];
        if ($detailProducts) {
            foreach ($detailProducts as $detail) {
                // $productIds[] =  $detail['product_id'];
                $productIds[] =  $detail->product_id;
            }
        }
        $productIds = implode(',', $productIds);

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
            if (!is_null($productIds)) {
                $sql .= " AND md.product_id in ($productIds)";
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

            $sql .= " AND md.product_id in ($productIds)";


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
                            } elseif ($secondMovement['movement_type'] == 3) {
                                $productStock = $secondMovement['qty'];
                            } elseif ($secondMovement['movement_type'] == 4) {
                                $productStock += $secondMovement['qty'];
                            } elseif ($secondMovement['movement_type'] == 5) {
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

    public function generateStorageInventory($branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, $date)
    {
        $movements = $this->generateMultiKardex(null, $date, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts);
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
                        } elseif ($secondMovement['movement_type'] == 3) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5) {
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array('category_id' => $movement['category_id'], 'category_code' => $movement['category_code'], 'category_name' => $movement['category_name'], 'product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'], 'line_id' => $movement['line_id'], 'line_code' => $movement['line_code'], 'line_name' => $movement['line_name'], 'stock' => $productStock));
            }
        }
        return $stock;
    }

    public function generateStorageInventoryv3($branchOfficeId, $storageId, $categoryId, $lineId, $productId, $markId, $request, $user = null)
    {
        if ($user == null) {
            $validUser = Auth::getUserInfo($this->config);
        } else {
            $validUser = Users::findFirst($user);
        }
        $and = "";
        $where = 'WHERE p.id > 0 ';
        if ($validUser->role_id == 26) {
            $and = "INNER JOIN sys_supercluster on sys_supercluster.id = bo.cluster_id and sys_supercluster.id = " . $validUser->cluster_id;
        } else {
            $where = $validUser->role_id == 1 ? '' : " and s.branch_office_id = $validUser->branch_office_id ";
        }

        if ($categoryId != 'TODOS' && $categoryId != '') {
            $where .= " AND l.category_id = $categoryId";
        }
        if ($lineId != 'TODOS' && $lineId != '') {
            $where .= " AND l.id = $lineId";
        }
        if ($productId != 'TODOS' && $productId != '') {
            $where .= " AND p.id = $productId";
        }
        if ($markId != 'TODOS' && $markId != '') {
            $where .= " AND ma.id = $markId";
        }
        if ($branchOfficeId != 'TODOS' && $branchOfficeId != '') {
            $where .= " AND s.branch_office_id = $branchOfficeId ";
        }
        if ($storageId != 'TODOS' && $storageId != '') {
            $where .= " AND ps.storage_id = $storageId ";
        }

        $sortBy = "";
        // $filter = $request['filter'];
        $pagination = $request['pagination'];
        // if (!empty($filter)){
        //     $where .= " AND ( p.id::text ILIKE '%".$filter."%' OR p.name ILIKE '%".$filter."%' OR p.old_code ILIKE '%".$filter."%' OR l.name ILIKE '%".$filter."%' OR c.name ILIKE '%".$filter."%' OR CONCAT(c.code,'-',l.code,'-',p.old_code) ILIKE '%".$filter."%' OR p.description ILIKE '%".$filter."%')";
        // }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= "";
            switch ($pagination['sortBy']) {
                case 'category':
                    $sortBy .= " ORDER BY c.name";
                    break;
                case 'line':
                    $sortBy .= " ORDER BY l.name";
                    break;
                case 'product':
                    $sortBy .= " ORDER BY p.name";
                    break;
                case 'code':
                    $sortBy .= " ORDER BY c.code||'-'||l.code||'-'||p.code";
                    break;
                case 'stock':
                    $sortBy .= " ORDER BY sum(ps.stock)";
                    break;
                default:
                    # code...
                    break;
            }
        } else {
            $sortBy .= " ORDER BY p.old_code ";
        }
        $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = empty($pagination['rowsPerPage']) ? "" : " LIMIT " . $pagination['rowsPerPage'];
        $sql = "SELECT count(distinct p.id) AS count
        from v_product_stock_price ps
        JOIN wms_storages AS s ON s.id = ps.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id $and
        JOIN wms_products AS p ON p.id = ps.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        $where";
        // var_dump($sql);
        $productsCount = $this->db->query($sql)->fetch();

        $sql = "select  l.category_id, c.code AS category_code, c.name AS category_name,ps.product_id, p.code AS product_code, p.name AS product_name,
        p.line_id, l.code AS line_code, l.name AS line_name,p.active as product_status,sum(ps.stock) as stock,ps.price,s.name as almacen, s.id AS starage_id
        from v_product_stock_price ps
        JOIN wms_storages AS s ON s.id = ps.storage_id 
        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
        JOIN wms_products AS p ON p.id = ps.product_id
        JOIN wms_lines AS l ON l.id = p.line_id
        JOIN wms_categories AS c ON c.id = l.category_id
        $where
        group by l.category_id, c.code, c.name,ps.product_id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ps.price,s.name,s.id
        {$sortBy} {$desc} {$limit} {$offset} ";
        $data = $this->db->query($sql)->fetchAll();
        // var_dump($sql);

        for ($i = 0; $i < count($data); $i++) {
            $sqlPrice = "SELECT TRUNC((md.unit_price)::numeric,2)  as price
           FROM wms_movement_details AS md
           INNER JOIN wms_movements AS m ON m.id = md.movement_id
           WHERE m.type_id = 1 AND md.product_id = " . $data[$i]['product_id'] . " AND m.storage_id =" . $data[$i]['starage_id'] . "
           ORDER BY md.id DESC LIMIT 1";
            $dataPrice = $this->db->query($sqlPrice)->fetch();

            $data[$i]['price'] = $dataPrice['price'];
        }
        // var_dump($sql);

        $response = array('data' => $data, 'rowCounts' => $productsCount['count']);
        return $response;
    }

    public function generateStorageInventoryv2($branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, $date)
    {
        $movements = $this->generateKardex(null, $date, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, null, null);
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
                        } elseif ($secondMovement['movement_type'] == 3) {
                            $productStock = $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5) {
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array('category_id' => $movement['category_id'], 'category_code' => $movement['category_code'], 'category_name' => $movement['category_name'], 'product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'], 'line_id' => $movement['line_id'], 'line_code' => $movement['line_code'], 'line_name' => $movement['line_name'], 'product_status' => $movement['status_product'], 'stock' => $productStock));
            }
        }
        return $stock;
    }

    public function generateStorageInventoryv2byMark($branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, $date, $user)
    {
        $movements = $this->generateKardexBymark(null, $date, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, null, $user);
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
                        } elseif ($secondMovement['movement_type'] == 3) {
                            $productStock = $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5) {
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array('category_id' => $movement['category_id'], 'category_code' => $movement['category_code'], 'category_name' => $movement['category_name'], 'product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'], 'line_id' => $movement['line_id'], 'line_code' => $movement['line_code'], 'line_name' => $movement['line_name'], 'product_status' => $movement['status_product'], 'stock' => $productStock));
            }
        }
        return $stock;
    }


    public function getReportPurchasesSQL($request)
    {
        $y = date('Y');
        $where = "";
        $order = "";

        $sortBy = "";
        $pagination = $request['pagination'];
        $filter = $request['filter'];

        if (!empty($filter['searchbar'])) {
            $where = "WHERE (code ILIKE '%" . $filter['searchbar'] . "%' or old_code ILIKE '%" . $filter['searchbar'] . "%' or p.name ILIKE '%" . $filter['searchbar'] . "%')";
        }

        if (!empty($filter['product'])) {
            $where = empty($where) ? "WHERE" : $where . " AND";
            $where .= " p.id = {$filter['product']}";
        }

        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'] . " ";

        $sql = "SELECT count(*) from wms_products";
        $productsCount = $this->db->query($sql)->fetchAll();

        $sql = "SELECT DISTINCT ON (p.id) 
                    p.id, 
                    code, 
                    old_code, 
                    p.name as product, 
                    requested_date, 
                    price, 
                    s.name as supplier 
                from wms_products as p 
                left join pur_order_details as od on od.product_id = p.id
                left join pur_orders as o on o.id = od.po_id
                left join pur_suppliers as s on s.id = o.supplier_id
                {$where}
                order by p.id, requested_date desc
                {$offset} {$limit}
                ";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data, 'rowCounts' => $productsCount[0]['count']);
        return $response;
    }

    public function getActiveProducts($pt = 0)
    {
        if ($this->userIsClient()) {
            $validUser = Auth::getUserData($this->config);
            $customerUser = CustomerUsers::findFirst("user_id = $validUser->id");
            if ($customerUser) {
                $customer = Customers::findFirst($customerUser->customer_id);
                $sql = "SELECT p.id, p.line_id,p.code, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-') AS product_code, p.name, p.photo, p.active, pp.price
                        FROM wms_products AS p
                        INNER JOIN wms_lines AS l
                        ON p.line_id = l.id
                        INNER JOIN wms_categories AS c
                        ON l.category_id = c.id
                        INNER JOIN wms_products_prices AS pp
                        ON pp.product_id = p.id AND pp.price_level = '$customer->price_list'
                        WHERE p.active;";
                $data = $this->db->query($sql);
                // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $content['products'] = $data->fetchAll();
            }
        } else {
            $sql = "SELECT p.id, p.line_id, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-',p.code) AS code, p.name, p.photo, p.active
                    FROM wms_products AS p
                    INNER JOIN wms_lines AS l
                    ON p.line_id = l.id
                    INNER JOIN wms_categories AS c
                    ON l.category_id = c.id
                    WHERE p.active;";
            $data = $this->db->query($sql);
            // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $content['products'] = $data->fetchAll();
        }

        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function generateStorageInventoryMinimal($branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, $date)
    {
        // print_r($user);
        // exit();
        $movements = $this->generateKardexMinimal(null, $date, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, null, null);
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
                        } elseif ($secondMovement['movement_type'] == 3) {
                            $productStock = $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5) {
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array('category_id' => $movement['category_id'], 'category_code' => $movement['category_code'], 'category_name' => $movement['category_name'], 'product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'], 'line_id' => $movement['line_id'], 'line_code' => $movement['line_code'], 'line_name' => $movement['line_name'], 'product_status' => $movement['status_product'], 'stock' => $productStock));
            }
        }
        return $stock;
    }
    public function generateStorageInventoryMinimalPdf($branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, $date, $user)
    {
        // print_r($user);
        // exit();
        $movements = $this->generateKardexMinimalPdf(null, $date, $branchOfficeId, $storageId, $categoryId, $lineId, $detailProducts, null, null, $user);
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
                        } elseif ($secondMovement['movement_type'] == 3) {
                            $productStock = $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 4) {
                            $productStock += $secondMovement['qty'];
                        } elseif ($secondMovement['movement_type'] == 5) {
                            $productStock -= $secondMovement['qty'];
                        }
                    }
                }
                array_push($products, $movement['product_id']);
                array_push($stock, array('category_id' => $movement['category_id'], 'category_code' => $movement['category_code'], 'category_name' => $movement['category_name'], 'product_id' => $movement['product_id'], 'product_code' => $movement['product_code'], 'product_name' => $movement['product_name'], 'line_id' => $movement['line_id'], 'line_code' => $movement['line_code'], 'line_name' => $movement['line_name'], 'product_status' => $movement['status_product'], 'stock' => $productStock));
            }
        }
        return $stock;
    }
    public function getGridSQL($branchOfficeId, $storageId, $category, $line, $request, $product, $mark)
    {
        $where = 'WHERE ps.product_id > 0 ';
        if ($category == 'TODOS') {
            $where .= " AND (l.category_id = {$category} OR {$category} IS NULL)";
        } else if ($category == '') {
        } else {
            $where .= " AND (l.category_id = {$category} OR {$category} IS NULL)";
        }
        if ($line == 'TODOS') {
            $where .= " AND (l.id = {$line} OR {$line} IS NULL)";
        } else if ($line == '') {
        } else {
            $where .= " AND (l.id = {$line} OR {$line} IS NULL)";
        }
        if ($branchOfficeId == 'TODOS') {
            "AND (bo.id = {$branchOfficeId} OR {$branchOfficeId} IS NULL)";
        } else if ($branchOfficeId == '') {
        } else {
            $where .= "AND (bo.id = {$branchOfficeId} OR {$branchOfficeId} IS NULL)";
        }
        if ($storageId == 'TODOS') {
            "AND (ps.storage_id = {$storageId} OR {$storageId} IS NULL)";
        } else if ($storageId == '') {
        } else {
            $where .= " AND (ps.storage_id = {$storageId} OR {$storageId} IS NULL)";
        }
        if ($product == 'TODOS') {
            $where .= " AND (ps.product_id = {$product} OR {$product} IS NULL)";
        } else if ($product == '') {
        } else {
            $where .= " AND (ps.product_id = {$product} OR {$product} IS NULL)";
        }
        if ($mark == 'TODOS') {
        } else if ($mark == '') {
        } else {
            $where .= " AND (ma.id = {$mark} OR {$mark} IS NULL)";
        }
        $sortBy = "";
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];
        if (!empty($filter)) {
            $where .= " AND ( p.id::text ILIKE '%" . $filter . "%' OR p.name ILIKE '%" . $filter . "%' OR p.old_code ILIKE '%" . $filter . "%' OR l.name ILIKE '%" . $filter . "%' OR c.name ILIKE '%" . $filter . "%' OR CONCAT(c.code,'-',l.code,'-',p.old_code) ILIKE '%" . $filter . "%' OR p.description ILIKE '%" . $filter . "%')";
        }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= "";
            if ($pagination['sortBy'] == 'category') {
                $sortBy .= " ORDER BY c.name";
            }
            if ($pagination['sortBy'] == 'line') {
                $sortBy .= " ORDER BY l.name";
            }
            if ($pagination['sortBy'] != 'line' && $pagination['sortBy'] != 'category') {
                $sortBy .= " ORDER BY p." . trim($pagination['sortBy']);
            }
        } else {
            $sortBy .= " ORDER BY p.old_code ";
        }
        $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];
        $sql = "SELECT count(p.id) AS count
                FROM v_product_stock_price AS ps
                LEFT JOIN wms_products as p on p.id = ps.product_id
                INNER JOIN wms_lines AS l 
                ON p.line_id = l.id
                INNER JOIN wms_categories AS c 
                ON l.category_id = c.id
                LEFT JOIN wms_products_minimum_stock AS pm
                ON p.id = pm.product_id and ps.storage_id = pm.storage_id
                LEFT JOIN wms_branch_offices AS bo
                ON bo.id = pm.branch_offices_id
                LEFT JOIN wms_storages AS s 
                ON s.id = pm.storage_id
                INNER JOIN wms_marks AS ma
                ON p.mark_id = ma.id
            {$where}
            group by l.category_id, c.code, c.name,ps.product_id,s.id,bo.id,bo.name,pm.stock,p.id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name
";
        // print_r($sql);
        ///exit();
        $productsCount = $this->db->query($sql)->fetchAll();
        $sql = "SELECT p.id, p.line_id, CONCAT(c.code,'-',l.code,'-',p.code) as old_code, CONCAT(c.code,'-',l.code,'-',p.code) as code, l.name AS line, l.category_id, c.name AS category, CONCAT(c.code,'-',l.code,'-') AS product_codes, p.name as product_name, p.photo, p.active, p.clave_producto_id,p.description,p.mark_id,rebasa_code,ma.name as marca,
        c.code as category_code,l.code as line_code,p.code as product_code, pm.stock as minimal_stock,bo.name as branch_office_name, s.name as storage_name,0 as stock,bo.id as branch_office_id,ps.storage_id as storage_id
                FROM v_product_stock_price AS ps
                LEFT JOIN wms_products as p on p.id = ps.product_id
                LEFT JOIN wms_lines AS l 
                ON p.line_id = l.id
                LEFT JOIN wms_categories AS c 
                ON l.category_id = c.id
                LEFT JOIN wms_products_minimum_stock AS pm
                ON p.id = pm.product_id and ps.storage_id = pm.storage_id
                LEFT JOIN wms_storages AS s 
                ON s.id = ps.storage_id
                LEFT JOIN wms_branch_offices AS bo
                ON bo.id = s.branch_office_id
                LEFT JOIN wms_marks AS ma
                ON p.mark_id = ma.id
                {$where} 
                group by l.category_id, c.code, c.name,ps.product_id,s.id,bo.id,bo.name,pm.stock,p.id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ma.name,ps.price,s.name,ps.storage_id

                {$sortBy} {$desc} {$offset} {$limit} ;";
        // print_r($sql);
        //exit();
        $data = $this->db->query($sql)->fetchAll();
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['storage_id'] != null) {
                $stock = $this->generateStorageInventoryv3(null, $data[$i]['storage_id'], $data[$i]['category_id'], $data[$i]['line_id'], $data[$i]['id'], null, $request, null);
                // generateStorageInventoryv3 ($branchOfficeId, $storageId, $categoryId, $lineId, $productId,$markId, $request, $user = null)
                //echo "<pre>";
                //print_r($stock);
                //exit();
                if ($stock) {
                    $data[$i]['stock'] = $stock['data'][0]['stock'];
                    // print_r($stock['data'][0]['stock']);
                }
            }
        }
        $response = array('data' => $data, 'rowCounts' => count($productsCount));
        // exit();
        return $response;
    }

    public function getGridSQLbyMark($branchOfficeId, $storageId, $category, $line, $request, $product, $mark)
    {
        //print_r($request);
        //exit();
        $where = 'WHERE p.id > 0 ';
        if ($category == 'TODOS') {
        } else if ($category == '') {
        } else {
            $where .= " AND l.category_id = $category";
        }
        if ($line == 'TODOS') {
        } else if ($line == '') {
        } else {
            $where .= " AND l.id = $line";
        }
        if ($product == 'TODOS') {
        } else if ($product == '') {
        } else {
            $where .= " AND pm.product_id = $product";
        }
        if ($mark == 'TODOS') {
        } else if ($mark == '') {
        } else {
            $where .= " AND ma.id = $mark";
        }
        $sortBy = "";
        $filter = $request['filter'];
        $pagination = $request['pagination'];
        if (!empty($filter)) {
            $where .= " AND ( p.id::text ILIKE '%" . $filter . "%' OR p.name ILIKE '%" . $filter . "%' OR p.old_code ILIKE '%" . $filter . "%' OR l.name ILIKE '%" . $filter . "%' OR c.name ILIKE '%" . $filter . "%' OR CONCAT(c.code,'-',l.code,'-',p.old_code) ILIKE '%" . $filter . "%' OR p.description ILIKE '%" . $filter . "%')";
        }
        if (!empty($pagination['sortBy'])) {
            $sortBy .= "";
            if ($pagination['sortBy'] == 'category') {
                $sortBy .= " ORDER BY c.name";
            }
            if ($pagination['sortBy'] == 'line') {
                $sortBy .= " ORDER BY l.name";
            }
            if ($pagination['sortBy'] != 'line' && $pagination['sortBy'] != 'category') {
                $sortBy .= " ORDER BY p." . trim($pagination['sortBy']);
            }
        } else {
            $sortBy .= " ORDER BY p.old_code ";
        }
        $desc = $pagination['descending'] === 'false' ? ' DESC ' : ' ASC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        // $limit = " LIMIT " . $pagination['rowsPerPage'];
        $sql = "SELECT count(p.id) AS count
                FROM wms_products AS p
                INNER JOIN wms_lines AS l 
                ON p.line_id = l.id
                INNER JOIN wms_categories AS c 
                ON l.category_id = c.id
                INNER JOIN wms_marks AS ma
                ON p.mark_id = ma.id
            {$where}";
        $productsCount = $this->db->query($sql)->fetchAll();
        $sql = "SELECT p.id, p.line_id, CONCAT(c.code,'-',l.code,'-',p.code) as old_code, CONCAT(c.code,'-',l.code,'-',p.code) as code, l.name AS line_name, l.category_id, c.name AS category_name, CONCAT(c.code,'-',l.code,'-') AS product_codes, p.name as  product_name, p.photo, p.active, p.clave_producto_id,p.description,p.mark_id,rebasa_code,ma.name as marca, c.code  as category_code,l.code as line_code,p.code as product_code, 0 as stock
                FROM wms_products AS p
                INNER JOIN wms_lines AS l 
                ON p.line_id = l.id
                INNER JOIN wms_categories AS c 
                ON l.category_id = c.id
                INNER JOIN wms_marks AS ma
                ON p.mark_id = ma.id
                {$where} {$sortBy} {$desc} {$offset} ;";
        //print_r($sql);
        //exit();
        $data = $this->db->query($sql)->fetchAll();
        for ($i = 0; $i < count($data); $i++) {
            $stock = $this->generateStorageInventoryv2byMark($branchOfficeId, $storageId, $data[$i]['category_id'], $data[$i]['line_id'], $data[$i]['id'], null, null);
            if ($stock) {
                $data[$i]['stock'] = $stock[0]['stock'];
            }
        }
        $response = array('data' => $data, 'rowCounts' => $productsCount[0]['count']);
        return $response;
    }


    public function getDataProducts()
    {
        $request = $this->request->getPost();
        $sql = $this->getDataProductsv1SQL($request['product']);
        $sql2 = $this->getDataProductsv2SQL($request['product'], $request['category'], $request['branchoffice'], $request['storage']);

        $this->content['product'] = $sql;
        $this->content['productData'] = $sql2;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE (role_id = 1 OR role_id = 4 OR role_id = 2 OR role_id = 3 OR role_id = 20 OR role_id = 17 OR role_id = 22 OR role_id = 27 OR role_id = 29 OR role_id = 28 OR role_id = 25 OR role_id = 26)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
    private function userHasPermission2()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE (role_id = 1)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    // ZONA DE CONSULTAS
    public function getDataProductsv1SQL($product)
    {
        if ($product) {
            $where = "WHERE p.id = $product";
            $sql = "SELECT p.name as product, l.name as line, c.name as category
                    FROM wms_products AS p
                    JOIN wms_lines as l on l.id = p.line_id
                    JOIN wms_categories as c on c.id = l.category_id
                    {$where}
                    ORDER BY p.id ASC;";
            $data = $this->db->query($sql)->fetchAll();
        }
        return $data;
    }

    public function getDataProductsv2SQL($product, $category, $office, $storage)
    {
        if ($category == 6) {
            $where = "WHERE m.status = 1 ";
            if ($product) {
                $where .= " AND p.id = $product";
            }
            if ($category) {
                $where .= " AND c.id = $category";
            }
            if ($office) {
                $where .= " AND bo.id = $office";
            }
            if ($storage) {
                $where .= " AND s.id = $storage";
            }
            $sql = "SELECT b.id, b.qty, bo.name AS office, s.name AS sucursal, p.id as product, m.type as movement_type, s.id as storage_id
                    FROM wms_bales AS b
                    JOIN wms_products AS p ON p.id = b.product_id
                    JOIN wms_movement_details AS md ON md.bale_id = b.id
                    JOIN wms_movements AS m ON m.id = md.movement_id
                    JOIN wms_storages AS s ON s.id = m.storage_id
                    JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
                    JOIN wms_lines AS l ON l.id = p.line_id
                    JOIN wms_categories AS c ON c.id = l.category_id
                    {$where}
                    ORDER BY b.id ASC, m.type ASC, s.id ASC,bo.name ASC;";
            $data = $this->db->query($sql)->fetchAll();
            $products = [];
            $stock = [];
            foreach ($data as $movement) {
                if (!in_array($movement['id'], $products)) {
                    $productStock = true;
                    foreach ($data as $secondMovement) {
                        if ($movement['id'] == $secondMovement['id'] && $movement['storage_id'] == $secondMovement['storage_id']) {
                            if ($secondMovement['movement_type'] == 1) {
                                //                                $productStock = true;
                            } else if ($secondMovement['movement_type'] == 2) {
                                $productStock = false;
                            }
                        }
                    }
                    if ($productStock) {
                        array_push($products, $movement['id']);
                        array_push($stock, array('id' => $movement['id'], 'qty' => $movement['qty'], 'office' => $movement['office'], 'sucursal' => $movement['sucursal']));
                    }
                }
            }
        } else if ($category == 3) {
            $where = "WHERE m.status = 1 and b.status = 'RECIBIDO' ";
            if ($product) {
                $where .= " AND p.id = $product";
            }
            if ($category) {
                $where .= " AND c.id = $category";
            }
            if ($office) {
                $where .= " AND bo.id = $office";
            }
            if ($storage) {
                $where .= " AND s.id = $storage";
            }
            $sql = "SELECT sd.id, md.qty, bo.name AS office, s.name AS sucursal, p.id as product, m.type as movement_type, s.id as storage_id
                    FROM pur_shipments AS b
                        JOIN pur_shipment_details as sd ON sd.shipment_id = b.id
                        JOIN wms_products AS p ON p.id = sd.product_id
                        JOIN wms_movement_details AS md ON md.bag_id = sd.id
                        JOIN wms_movements AS m ON m.id = md.movement_id
                        JOIN wms_storages AS s ON s.id = m.storage_id
                        JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
                        JOIN wms_lines AS l ON l.id = p.line_id
                        JOIN wms_categories AS c ON c.id = l.category_id
                    {$where}
                    ORDER BY sd.id ASC , m.type ASC ,bo.name ASC;";
            $data = $this->db->query($sql)->fetchAll();
            $products = [];
            $stock = [];
            foreach ($data as $movement) {
                if (!in_array($movement['id'], $products)) {
                    $productStock = true;
                    $pesoproducto = 0;
                    foreach ($data as $secondMovement) {
                        if ($movement['id'] === $secondMovement['id'] && $movement['storage_id'] === $secondMovement['storage_id']) {
                            if ($movement['qty'] === $secondMovement['qty']) {
                                if ($secondMovement['movement_type'] == 1) {
                                    $pesoproducto += $secondMovement['qty'];
                                } else if ($secondMovement['movement_type'] == 2) {
                                    $productStock = false;
                                    $pesoproducto -= $secondMovement['qty'];
                                }
                            } else {
                                if ($secondMovement['movement_type'] == 1) {
                                    $pesoproducto += $secondMovement['qty'];
                                } else if ($secondMovement['movement_type'] == 2) {
                                    $pesoproducto -= $secondMovement['qty'];
                                }
                            }
                        }
                    }
                    if ($productStock) {
                        array_push($products, $movement['id']);
                        array_push($stock, array('id' => $movement['id'], 'qty' => $pesoproducto, 'office' => $movement['office'], 'sucursal' => $movement['sucursal']));
                    }
                }
            }
        }
        return $stock;
    }

    private function generateStorageInventoryv4($storageId, $productId)
    {
        $where = 'WHERE p.id > 0 ';
        $where .= " AND p.id = $productId";
        $where .= " AND ps.storage_id = $storageId ";

        $sql = "select  l.category_id, c.code AS category_code, c.name AS category_name,ps.product_id, p.code AS product_code, p.name AS product_name,
                p.line_id, l.code AS line_code, l.name AS line_name,p.active as product_status,sum(ps.stock) as stock,ps.price,s.name as almacen, s.id AS starage_id
                from v_product_stock_price ps
                JOIN wms_storages AS s ON s.id = ps.storage_id 
                JOIN wms_branch_offices AS bo ON bo.id = s.branch_office_id
                JOIN wms_products AS p ON p.id = ps.product_id
                JOIN wms_lines AS l ON l.id = p.line_id
                JOIN wms_categories AS c ON c.id = l.category_id
                $where
                group by l.category_id, c.code, c.name,ps.product_id, p.code, p.name, p.line_id, l.code, l.name,p.active,p.old_code,ps.price,s.name,s.id";
        $data = $this->db->query($sql)->fetchAll();
        // var_dump($sql);
        return $data;
    }

    public function movementPdf($id)
    {
        $id = intval($id);

        $sqlTypeMovement = "SELECT type_id, movement_id FROM wms_movements WHERE id = $id";
        $queryTypeMovement = $this->db->query($sqlTypeMovement)->fetch();
        $almacenSEntrada = "";
        if ($queryTypeMovement['type_id'] == 4 || $queryTypeMovement['type_id'] == 5) {
            //var_dump($queryTypeMovement['movement_id']);
            if ($queryTypeMovement['movement_id'] != null) {
                $sql = "SELECT m.folio AS folio, m.date AS fecha,md.qty AS cantidad, (SELECT SUM(unit_price * qty) FROM wms_movement_details as md INNER JOIN wms_movements AS m ON md.movement_id = m.id WHERE m.id = $id) as totalgen, (SELECT SUM(qty) FROM wms_movement_details AS md WHERE md.movement_id = " . $queryTypeMovement['movement_id'] . ") as cantidadgen, md.unit_price AS precio, md.unit_price * md.qty as total, u.name AS unidad, bo.name, m.status AS estado, s.name AS almacen, p.name AS productos FROM wms_movements AS m
        INNER JOIN wms_storages AS s ON m.storage_id = s.id
        INNER JOIN wms_movement_details AS md ON m.id = md.movement_id
        INNER JOIN wms_products AS p ON md.product_id = p.id
        INNER JOIN wms_branch_offices AS bo ON s.branch_office_id = bo.id
        INNER JOIN wms_units AS u ON p.unit_id = u.id
        WHERE m.id = " . $queryTypeMovement['movement_id'];
            } else {
                $sql = "SELECT m.folio AS folio, m.date AS fecha,md.qty AS cantidad, (SELECT SUM(unit_price * qty) FROM wms_movement_details as md INNER JOIN wms_movements AS m ON md.movement_id = m.id WHERE m.id = $id) as totalgen, (SELECT SUM(qty) FROM wms_movement_details AS md WHERE md.movement_id = " . $id . ") as cantidadgen, md.unit_price AS precio, md.unit_price * md.qty as total, u.name AS unidad, bo.name, m.status AS estado, s.name AS almacen, p.name AS productos FROM wms_movements AS m
        INNER JOIN wms_storages AS s ON m.storage_id = s.id
        INNER JOIN wms_movement_details AS md ON m.id = md.movement_id
        INNER JOIN wms_products AS p ON md.product_id = p.id
        INNER JOIN wms_branch_offices AS bo ON s.branch_office_id = bo.id
        INNER JOIN wms_units AS u ON p.unit_id = u.id
        WHERE m.id = $id";

                $sqlAlmacen = "SELECT wmsst.name FROM wms_movements
		inner join wms_storages as wmsst on wmsst.id = wms_movements.storage_id
		where wms_movements.movement_id = $id";
                $dataAlmacen = $this->db->query($sqlAlmacen)->fetchAll();
                $almacenSEntrada = $dataAlmacen[0]["name"];
            }
        } else {
            $sql = "SELECT m.folio AS folio, m.date AS fecha,md.qty AS cantidad, (SELECT SUM(unit_price * qty) 
        FROM wms_movement_details as md INNER JOIN wms_movements AS m ON md.movement_id = m.id WHERE m.id = $id) as totalgen,
        (SELECT SUM(qty) FROM wms_movement_details AS md WHERE md.movement_id = $id) as cantidadgen, md.unit_price AS precio, md.unit_price * md.qty as total, 
        u.name AS unidad, bo.name, m.status AS estado, s.name AS almacen, p.name AS productos, p.id AS product_id
        FROM wms_movements AS m
        INNER JOIN wms_storages AS s ON m.storage_id = s.id
        INNER JOIN wms_movement_details AS md ON m.id = md.movement_id
        INNER JOIN wms_products AS p ON md.product_id = p.id
        INNER JOIN wms_branch_offices AS bo ON s.branch_office_id = bo.id
        INNER JOIN wms_units AS u ON p.unit_id = u.id
        WHERE m.id = " . $id;
        }

        // var_dump($sql);
        $data = $this->db->query($sql)->fetchAll();

        $sqlDos = "SELECT m.id, m.storage_id, s.name AS storage,s.branch_office_id AS branch_id, b.name as branch, m.type_id, m.date::date AS original_date,TO_CHAR(m.date :: DATE, 'dd/mm/yyyy') as date, m.status,m.folio
        FROM wms_movements AS m
        INNER JOIN wms_storages AS s
        ON m.storage_id = s.id
        INNER JOIN wms_branch_offices as b
        ON s.branch_office_id = b.id
        WHERE m.id =" . $id;
        $dataHed = $this->db->query($sqlDos)->fetchAll();


        if ($queryTypeMovement['type_id'] == 3) {
            for ($i=0; $i < count($data); $i++) { 
                $inventoryStock = $this->generateStorageInventoryv4($dataHed[0]['branch_id'],$dataHed[0]['storage_id'],$data[$i]['product_id'],$dataHed[0]['original_date']);
                $data[$i]['stock'] = $inventoryStock[0]['stock'];
            }
        }

        $titulo = "";
        if ($dataHed[0]['type_id'] == 1) {
            $titulo = "ENTRADAS DE ALMACÉN";
        }

        if ($dataHed[0]['type_id'] == 2) {
            $titulo = "SALIDA DE ALMACÉN";
        }

        if ($dataHed[0]['type_id'] == 3) {
            $titulo = "INVENTARIO FÍSICO";
        }

        if ($dataHed[0]['type_id'] == 5) {
            $titulo = "TRANSPASO (SALIDA)";
        }
        if ($dataHed[0]['type_id'] == 4) {
            $titulo = "TRANSPASO (ENTRADA)";
        }

        $widths = array(10, 70, 29, 29, 29, 29, 29);
        $aligns = array('C', 'L', 'L', 'R', 'R', 'R');

        if($dataHed[0]['type_id'] == 3){
            $widths = array(10, 75, 29, 29, 29, 29, 29);
            $aligns = array('C', 'L', 'L', 'R', 'R', 'R');
        }


        $pdf = new PDFMovement();
        $pdf->AliasNbPages();
        if($dataHed[0]['type_id'] == 3){
            $pdf->AddPage('L', 'Letter');
        } else {
            $pdf->AddPage('P', 'Letter');
        }
        $pdf->SetLineWidth(0.2);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        $pdf->SetTitle(utf8_decode($titulo));
        $pdf->encabezadoDriver($dataHed[0], utf8_decode($titulo), $data, $almacenSEntrada,$dataHed[0]);

        $pdf->SetTextColor(0);
        $pdf->Ln();
        $pdf->SetXY(8, 62);
        $pdf->SetFont('Arial', null, 8);
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        $fill = false;
        $y = 32;
        $i = 1;

        if($dataHed[0]['type_id'] == 3){
            foreach ($data as $kdx) {
                $pdf->SetX(8);
                $pdf->Row(array($i, $kdx['productos'], $kdx['unidad'], $kdx['cantidad'],"$" . $kdx['precio'] . ".00", "$" . number_format($kdx['total'], 2, '.', ',') . ""), $fill);
                $y += 6;
                $i++;
            }

        } else { 
        foreach ($data as $kdx) {
            $pdf->SetX(8);
            $pdf->Row(array($i, $kdx['productos'], $kdx['unidad'], $kdx['cantidad'], "$" . $kdx['precio'] . ".00","$" . number_format($kdx['total'], 2, '.', ',') . ""), $fill);
            $y += 6;
            $i++;
        }
        }  

        if($dataHed[0]['type_id'] == 3){
            
        } else {
            if (count($data)) {
                $pdf->Cell(78, $y);
                $pdf->Cell(58, 6, "Total", 1, 0, 'R');
                $pdf->Cell(29, 6, $data[0]['cantidadgen'] . " PZAS.", 1, 0, 'R');
                $pdf->Cell(29, 6, "$" . number_format($data[0]['totalgen'], 2, '.', ',') . "", 1, 0, 'R');
            } else {
                $pdf->Cell(78, $y);
                $pdf->Cell(58, 6, "Total", 1, 0, 'R');
                $pdf->Cell(29, 6, "0 PZAS.", 1, 0, 'R');
                $pdf->Cell(29, 6, "$" . number_format(0, 2, '.', ',') . "", 1, 0, 'R');
            }
        }


        $pdf->Output('I', 'drivers_reports.pdf', true);
        $response = new Phalcon\Http\Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Disposition', 'inline; filename=cdrivers_reports.pdf');
        return $response;
    }

    public function movementPdfsi($id)
    {
        $id = intval($id);
        $sql = "SELECT m.folio AS folio, m.date AS fecha,md.qty AS cantidad, 
        (SELECT SUM(unit_price * qty) FROM wms_movement_details as md INNER JOIN wms_movements AS m ON md.movement_id = m.id WHERE m.id = $id) as totalgen,
        (SELECT SUM(qty) FROM wms_movement_details AS md WHERE md.movement_id = $id) as cantidadgen, md.unit_price AS precio, md.unit_price * md.qty as total, 
        u.name AS unidad, bo.name, m.status AS estado, s.name AS almacen, p.name AS productos
        FROM wms_movements AS m
        INNER JOIN wms_storages AS s ON m.storage_id = s.id
        INNER JOIN wms_movement_details AS md ON m.id = md.movement_id
        INNER JOIN wms_products AS p ON md.product_id = p.id
        INNER JOIN wms_branch_offices AS bo ON s.branch_office_id = bo.id
        INNER JOIN wms_units AS u ON p.unit_id = u.id
        WHERE m.id = " . $id;
        $data = $this->db->query($sql)->fetchAll();

        $sqlDos = "SELECT m.id, m.storage_id, s.name AS storage,s.branch_office_id AS branch_id, b.name as branch, m.type_id, TO_CHAR(m.date :: DATE, 'dd/mm/yyyy') as date, m.status,m.folio
        FROM wms_movements AS m
        INNER JOIN wms_storages AS s
        ON m.storage_id = s.id
        INNER JOIN wms_branch_offices as b
        ON s.branch_office_id = b.id
        WHERE m.id =" . $id;
        $dataHed = $this->db->query($sqlDos)->fetchAll();

        $titulo = "";
        if ($dataHed[0]['type_id'] == 1) {
            $titulo = "ENTRADAS DE ALMACÉN";
        }

        if ($dataHed[0]['type_id'] == 2) {
            $titulo = "SALIDA DE ALMACÉN";
        }

        $widths = array(10, 100, 44, 44, 29);
        $aligns = array('C', 'L', 'L', 'R');

        $pdf = new PDFMovementSI();
        $pdf->AliasNbPages();
        $pdf->AddPage('P', 'Letter');
        $pdf->SetLineWidth(0.2);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        $pdf->SetTitle(utf8_decode($titulo));
        $pdf->encabezadoDriver($dataHed[0]);

        $pdf->SetTextColor(0);
        $pdf->Ln();
        $pdf->SetXY(8, 50);
        $pdf->SetFont('Arial', null, 8);
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        $fill = false;
        $y = 32;
        $i = 1;
        foreach ($data as $kdx) {
            $pdf->SetX(8);
            $pdf->Row(array($i, $kdx['productos'], $kdx['unidad'], $kdx['cantidad']), $fill);
            $y += 6;
            $i++;
        }
        $pdf->Cell(78, $y);
        $pdf->Cell(74, 6, "Total", 1, 0, 'R');
        $pdf->Cell(44, 6, $data[0]['cantidadgen'] . " PZAS.", 1, 0, 'R');

        $pdf->Output('I', 'drivers_reports.pdf', true);
        $response = new Phalcon\Http\Response();
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Disposition', 'inline; filename=cdrivers_reports.pdf');
        return $response;
    }
}

class PDFMovement extends FPDF
{
    var $widths;
    var $aligns;

    function encabezadoDriver($data, $movement, $data2, $almacenEntrada, $type)
    {
        // quite esto /public/assets/images/
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo_alpez_bn.png';
        $movimiento = "";
        $titulo = "";

        if (file_exists($logo)) {
            $this->Image($logo, 5, 10, 70, 0, 'PNG');
        }
        $this->SetFont('Arial', 'B', 10);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';


        if ($data['type_id'] == 3) {
            $this->SetXY(230, 20);
            $this->SetFont('Arial', '', 10);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 10, 'Fecha: ' . date('m') . '/' . date('d') . '/' . date('Y'));
            
            $this->SetXY(($this->GetPageWidth() / 2) - 25, 10);
            $this->SetFont('Arial', '', 15);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 10, utf8_decode($titulo));
            
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(10, 40);
            $this->Cell(0, 0, 'Folio: ' . $data['folio']);
            
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(110, 40);
            $this->Cell(0, 0, 'Movimiento: ' . $movement);
            
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(220, 40);
            $this->Cell(0, 0, 'Estatus: ' . $data['status']);
            
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(10, 43);
            $this->MultiCell(70, 5, 'Sucursal: ' . $data['branch']);
            
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(110, 43);
            
        } else {
            $this->SetXY(170, 10);
            $this->SetFont('Arial', '', 10);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 10, 'Fecha: ' . date('m') . '/' . date('d') . '/' . date('Y'));

            $this->SetXY(($this->GetPageWidth() / 2) - 25, 10);
            $this->SetFont('Arial', '', 15);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 10, utf8_decode($titulo));

            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(10, 40);
            $this->Cell(0, 0, 'Folio: ' . $data['folio']);

            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(90, 40);
            $this->Cell(0, 0, 'Movimiento: ' . $movement);

            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(162, 40);
            $this->Cell(0, 0, 'Estatus: ' . $data['status']);

            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(10, 43);
            $this->MultiCell(70, 5, 'Sucursal: ' . $data['branch']);

            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(90, 43);
        }

        if ($data['type_id'] == 4 || $data['type_id'] == 5) {
            $alm = "";
            if ($almacenEntrada == null) {
                $alm =  $data['storage'];
            } else {
                $alm = $almacenEntrada;
            }
            $this->MultiCell(60, 5, utf8_decode('Origen: ' . $data2[0]['almacen'] . '  Destino: ' . $alm));
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(162, 45);
            $this->Cell(0, 0, utf8_decode('Fecha ejecución: ' . $data['date']));
        } else if ($data['type_id'] == 3){
            $this->MultiCell(60, 5, utf8_decode('Almacén: ' . $data['storage']));
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(220, 45);
            $this->Cell(0, 0, utf8_decode('Fecha ejecución: ' . $data['date']));
        } else {
            $this->MultiCell(60, 5, utf8_decode('Almacén: ' . $data['storage']));
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(0);
            $this->SetXY(162, 45);
            $this->Cell(0, 0, utf8_decode('Fecha ejecución: ' . $data['date']));
        }


        $this->SetXY(8, 55);
        //30,136,229
        $this->SetFillColor(76, 175, 80);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B', 8);
        // Header
        $x = 168;
        $i = 0;
        $header = array('#', utf8_decode('Producto'), utf8_decode('Unidad'), utf8_decode('Cantidad'), utf8_decode('Costo'), utf8_decode('Importe'));
        $w = array(10, 70, 29, 29, 29, 29, 29);

        if ($data['type_id'] == 3) {
            $header = array('#', utf8_decode('Producto'), utf8_decode('Unidad'), utf8_decode('Cantidad'),utf8_decode('Costo'), utf8_decode('Importe'));
            $w = array(10, 75, 29, 29, 29, 29, 29);
        }

        foreach ($header as $col) {
            $this->Cell($w[$i], 7, $col, 1, 0, 'C', true);
            $i++;
        }
    }

    function Footer()
    {
        $this->SetFont('Arial', '', 10);
        $this->SetY(270);
        $this->SetFillColor(76, 175, 80);
        $this->SetTextColor(255, 255, 255);
        $this->Rect(0, 271, 279.4, 190, 'DF');
        $this->Cell(0, 10, utf8_decode('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $fill)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            if ($fill)
                $this->Rect($x, $y, $w, $h, 'DF');
            else
                $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, utf8_decode($data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if (($this->GetY() + $h) > $this->PageBreakTrigger) {
            $this->AddPage('L', 'Letter');
            $this->SetXY(8, 25);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}

class PDFMovementSI extends FPDF
{
    var $widths;
    var $aligns;

    function encabezadoDriver($data)
    {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo.png';
        $movimiento = "";
        $titulo = "";

        if ($data['type_id'] == 1) {
            $movimiento = "ENTRADA";
            $titulo = "ENTRADAS DE ALMACÉN";
        }

        if ($data['type_id'] == 2) {
            $movimiento = "SALIDA";
            $titulo = "SALIDA DE ALMACÉN";
        }

        if (file_exists($logo)) {
            $this->Image($logo, 10, 10, 60, 15);
        }
        $this->SetFont('Arial', 'B', 10);

        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $this->SetXY(170, 10);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, 'Fecha: ' . date('m') . '/' . date('d') . '/' . date('Y'));

        $this->SetXY(($this->GetPageWidth() / 2) - 25, 10);
        $this->SetFont('Arial', '', 15);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, utf8_decode($titulo));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(10, 30);
        $this->Cell(0, 0, 'Folio: ' . $data['folio']);

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(90, 30);
        $this->Cell(0, 0, 'Movimiento: ' . $movimiento);

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(162, 30);
        $this->Cell(0, 0, 'Estatus: ' . $data['status']);

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(10, 33);
        $this->MultiCell(70, 5, 'Sucursal: ' . $data['branch']);

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(90, 33);
        $this->MultiCell(60, 5, utf8_decode('Almacén: ' . $data['storage']));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(162, 36);
        $this->Cell(0, 0, utf8_decode('Fecha ejecución: ' . $data['date']));

        $this->SetXY(8, 43);
        $this->SetFillColor(30, 136, 229);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B', 8);
        // Header
        $x = 168;
        $i = 0;
        $header = array('#', utf8_decode('Producto'), utf8_decode('Unidad'), utf8_decode('Cantidad'));
        $w = array(10, 100, 44, 44, 29);

        foreach ($header as $col) {
            $this->Cell($w[$i], 7, $col, 1, 0, 'C', true);
            $i++;
        }
    }

    function Footer()
    {
        $this->SetFont('Arial', '', 10);
        $this->SetY(270);
        $this->SetFillColor(30, 136, 229);
        $this->SetTextColor(255, 255, 255);
        $this->Rect(0, 271, 279.4, 190, 'DF');
        $this->Cell(0, 10, utf8_decode('Page ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $fill)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            if ($fill)
                $this->Rect($x, $y, $w, $h, 'DF');
            else
                $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, utf8_decode($data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if (($this->GetY() + $h) > $this->PageBreakTrigger) {
            $this->AddPage('L', 'Letter');
            $this->SetXY(8, 25);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}



class PDFKardex extends FPDF
{
    var $widths;
    var $aligns;
    var $height;
    var $orderNumber;
    var $drawEdge = true;
    var $fillCell = false;

    function Header()
    {
        $date = new DateTime();
        $path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $img = $path . 'logo.png';
        $this->Image($img, 10, 5, 45, 0, 'PNG');
        $this->SetFont('Arial', 'B', 17);
        $this->SetX($this->GetX() + 100);
        $this->MultiCell(65, 10, utf8_decode("KARDEX"), 0, 'C', false);
        $this->SetXY(232, 10);
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, 'Fecha: ' . $date->format('d/m/Y'));
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(200);
        $this->Cell(275, 6, "www.empresa.mx", 0, 0, 'C', false);
        $this->SetFont('Arial', '', 10);
        $this->SetY(211);
        $this->SetFillColor(30, 136, 229);
        $this->SetTextColor(255);
        $this->Rect(0, 206, 300, 190, 'DF');
        $this->Cell(0, 0, utf8_decode('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function SetHeight($h)
    {
        $this->height = $h;
    }

    function SetOrderNumber($o)
    {
        $this->orderNumber = $o;
    }

    function SetDrawEdge($de)
    {
        $this->drawEdge = $de;
    }

    function SetFill($f)
    {
        $this->fill = $f;
    }

    function Row($data)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = $this->height * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $f = isset($this->fill[$i]) ? $this->fill[$i] : false;
            $x = $this->GetX();
            $y = $this->GetY();
            if ($this->drawEdge) {
                $this->Rect($x, $y, $w, $h);
            }
            $this->MultiCell($w, $this->height, $data[$i], 0, $a, $f);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}

class PDFInventory extends FPDF
{
    var $widths;
    var $aligns;

    function encabezadoDriver($titulo, $branchOfficeId, $storageId, $categoryId, $lineId, $productId, $date)
    {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        $nowDate = new DateTime();
        $branchOffice = "TODAS";
        $storage = "TODOS";
        $category = "TODAS";
        $line = "TODAS";
        $product = "TODOS";
        $dateShow = $nowDate->format('d/m/Y');
        if ($branchOfficeId != 'null') {
            $data = BranchOffices::findFirst(intval($branchOfficeId));
            $branchOffice = $data->name;
        }
        if ($storageId != 'null') {
            $data = Storages::findFirst(intval($storageId));
            $storage = $data->name;
        }
        if ($categoryId != 'null') {
            $data = Categories::findFirst(intval($categoryId));
            $category = $data->name;
        }
        if ($lineId != 'null') {
            $data = Lines::findFirst(intval($lineId));
            $line = $data->name;
        }
        if ($productId != 'null') {
            $data = Products::findFirst(intval($productId));
            $product = $data->name;
        }
        if ($date != 'null') {
            $dateIn = new DateTime($date);
            $dateShow = $dateIn->format('d/m/Y');
        }

        $this->AliasNbPages();
        $this->AddPage('L', 'Letter');
        $this->SetLineWidth(0.2);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetTitle(utf8_decode($titulo));

        if (file_exists($logo)) {
            $this->Image($logo, 10, 8, 50, 0, 'PNG');
        }
        $this->SetFont('Arial', 'B', 10);

        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/images/';
        $this->SetXY(241, 10);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, 'Fecha: ' . $dateShow);

        $this->SetXY(($this->GetPageWidth() / 2) - 25, 10);
        $this->SetFont('Arial', '', 25);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, utf8_decode($titulo));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(10, 40);
        $this->Cell(0, 0, utf8_decode('Sucursal: ' . $branchOffice));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(60, 40);
        $this->Cell(0, 0, utf8_decode('Almacén: ' . $storage));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(110, 40);
        $this->Cell(0, 0, utf8_decode('Categoria: ' . $category));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(180, 40);
        $this->Cell(0, 0, utf8_decode('Línea: ' . $line));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(10, 45);
        $this->MultiCell(150, 5, utf8_decode('Producto: ' . $product));

        $this->SetXY(8, 50);
        //30,136,229
        $this->SetFillColor(128, 179, 240);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B', 8);
        // Header
        $x = 168;
        $i = 0;
        $header = array('#', utf8_decode('CATEGORÍA'), utf8_decode('LÍNEA'), utf8_decode('PRODUCTO'), utf8_decode('CODIGO'), utf8_decode('MARCA'), utf8_decode('ALMACEN'), utf8_decode('PRECIO'), utf8_decode('EXISTENCIA'));
        $w = array(8, 25, 35, 55, 35, 35, 25, 25, 25, 25);

        foreach ($header as $col) {
            $this->Cell($w[$i], 7, $col, 1, 0, 'C', true);
            $i++;
        }
    }

    function Footer()
    {

        $this->SetFont('Arial', '', 10);
        $this->SetY(207);
        $this->SetFillColor(30, 136, 229);
        $this->SetTextColor(255);
        $this->Rect(0, 208, 279.4, 190, 'DF');
        $this->Cell(0, 10, utf8_decode('Page ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $fill)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            if ($fill)
                $this->Rect($x, $y, $w, $h, 'DF');
            else
                $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, utf8_decode($data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if (($this->GetY() + $h) > $this->PageBreakTrigger) {
            $this->AddPage('L', 'Letter');
            $this->SetXY(8, 25);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}

///

class PDFInventorystockMinimal extends FPDF
{
    var $widths;
    var $aligns;

    function encabezadoDriverMinimal($titulo, $branchOfficeId, $storageId, $categoryId, $lineId, $productId)
    {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $logo = $image_path . 'logo2.png';

        // $nowDate = new DateTime();
        $branchOffice = "TODAS";
        $storage = "TODOS";
        $category = "TODAS";
        $line = "TODAS";
        $product = "TODOS";
        // $dateShow = $nowDate->format('d/m/Y');
        if ($branchOfficeId != 'null') {
            $data = BranchOffices::findFirst(intval($branchOfficeId));
            $branchOffice = $data->name;
        }
        if ($storageId != 'null') {
            $data = Storages::findFirst(intval($storageId));
            $storage = $data->name;
        }
        if ($categoryId != 'null') {
            $data = Categories::findFirst(intval($categoryId));
            $category = $data->name;
        }
        if ($lineId != 'null') {
            $data = Lines::findFirst(intval($lineId));
            $line = $data->name;
        }
        if ($productId != 'null') {
            $data = Products::findFirst(intval($productId));
            $product = $data->name;
        }

        $this->AliasNbPages();
        $this->AddPage('L', 'Letter');
        $this->SetLineWidth(0.2);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetTitle(utf8_decode($titulo));

        if (file_exists($logo)) {
            $this->Image($logo, 10, 8, 50, 0, 'PNG');
        }
        $this->SetFont('Arial', 'B', 10);

        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/public/images/';
        $this->SetXY(241, 10);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, 'Fecha: ');

        $this->SetXY(($this->GetPageWidth() / 2) - 25, 10);
        $this->SetFont('Arial', '', 25);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, utf8_decode($titulo));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(10, 40);
        $this->Cell(0, 0, utf8_decode('Sucursal: ' . $branchOffice));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(60, 40);
        $this->Cell(0, 0, utf8_decode('Almacén: ' . $storage));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(110, 40);
        $this->Cell(0, 0, utf8_decode('Categoria: ' . $category));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(180, 40);
        $this->Cell(0, 0, utf8_decode('Línea: ' . $line));

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0);
        $this->SetXY(10, 45);
        $this->MultiCell(150, 5, utf8_decode('Producto: ' . $product));

        $this->SetXY(8, 50);
        //30,136,229
        $this->SetFillColor(128, 179, 240);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B', 8);
        // Header
        $x = 168;
        $i = 0;
        $header = array('#', utf8_decode('CATEGORÍA'), utf8_decode('LÍNEA'), utf8_decode('CÓDIGO
            '), utf8_decode('SUCURSAL'), utf8_decode('ALMACÉN'), utf8_decode('MARCA'), utf8_decode('PRODUCTO'), utf8_decode('MINIMO'), utf8_decode('EXISTENCIA'));
        $w = array(8, 35, 35, 35, 35, 25, 20, 25, 20, 20);

        foreach ($header as $col) {
            $this->Cell($w[$i], 7, $col, 1, 0, 'C', true);
            $i++;
        }
    }

    function Footer()
    {

        $this->SetFont('Arial', '', 10);
        $this->SetY(207);
        $this->SetFillColor(30, 136, 229);
        $this->SetTextColor(255);
        $this->Rect(0, 208, 279.4, 190, 'DF');
        $this->Cell(0, 10, utf8_decode('Page ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
        $this->Ln();
    }

    function SetWidths($w)
    {
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        $this->aligns = $a;
    }

    function Row($data, $fill)
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 6 * $nb;
        $this->CheckPageBreak($h);
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i];
            $x = $this->GetX();
            $y = $this->GetY();
            if ($fill)
                $this->Rect($x, $y, $w, $h, 'DF');
            else
                $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, 6, utf8_decode($data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if (($this->GetY() + $h) > $this->PageBreakTrigger) {
            $this->AddPage('L', 'Letter');
            $this->SetXY(8, 25);
        }
    }

    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}
//