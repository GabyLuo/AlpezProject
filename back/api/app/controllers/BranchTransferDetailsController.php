<?php

use Phalcon\Mvc\Controller;

class BranchTransferDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getRawMaterialsByTransactionId ($transactionId)
    {
        if (is_numeric($transactionId)) {
            $sql = "SELECT md.id, md.product_id, p.name AS product, md.qty, concat(c.code,'-',l.code,'-',p.name) as product_name
                    FROM wms_movement_details AS md
                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                    INNER JOIN wms_products AS p ON p.id = md.product_id
                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                    WHERE l.category_id = 14
                    AND m.type = 2
                    AND m.transaction_id = $transactionId
                    ORDER BY md.id ASC;";
            $rawMaterials = $this->db->query($sql)->fetchAll();
            $this->content['rawMaterials'] = $rawMaterials;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No se ha recibido un id de transacción válido.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ($pt = 0)
    {
        if ($this->userHasPermission()) {
            $tx = $this->transactions->get();

            $request = $this->request->getPost();

            if (isset($request['branchTransferId']) && is_numeric($request['branchTransferId'])) {
                $branchTransfer = BranchTransfers::findFirst($request['branchTransferId']);
                if ($branchTransfer) {
                    $exitMovement = Movements::findFirst("type = 2 AND transaction_id = $branchTransfer->transaction_id");
                    $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $branchTransfer->transaction_id");
                    if (isset($request['baleId']) && is_numeric($request['baleId'])) {
                        $exitMovementDetail = MovementDetails::findFirst("movement_id = $exitMovement->id AND bale_id = ".$request['baleId']);
                        if ($exitMovementDetail) {
                            $this->content['message'] = Message::error('La paca seleccionada ya se encuentra en el traspaso.');
                        } else {
                            $bale = Bales::findFirst($request['baleId']);
                            if ($bale) {
                                $product = Products::findFirst($bale->product_id);
                                if ($product->active) {
                                    $sql = "SELECT md.id, m.date
                                            FROM wms_movement_details AS md
                                            INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                            WHERE m.type = 1 AND m.status = 1 AND m.storage_id = $exitMovement->storage_id AND md.bale_id = ".$request['baleId']."
                                            ORDER BY date DESC
                                            LIMIT 1;";
                                    $baleEntryMovement = $this->db->query($sql)->fetch();
                                    if ($baleEntryMovement) {
                                        $sql = "SELECT value, product_id, qty, date
                                                FROM (
                                                    SELECT md.bale_id AS value, md.product_id, md.qty, m.date
                                                    FROM wms_movement_details AS md
                                                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                                    WHERE m.status = 1 AND m.type = 2 AND md.bale_id = ".$request['baleId']." AND m.storage_id = $exitMovement->storage_id AND m.date >= '".$baleEntryMovement['date']."'
                                                ) AS sub
                                                ORDER BY date DESC
                                                LIMIT 1;";
                                        $baleExitMovement = $this->db->query($sql)->fetch();
                                        if ($baleExitMovement) {
                                            $this->content['message'] = Message::error('La paca seleccionada ya no se encuentra disponible.');
                                        } else {
                                            $bale = Bales::findFirst($request['baleId']);
                                            $exitMovementDetail = new MovementDetails();
                                            $exitMovementDetail->movement_id = $exitMovement->id;
                                            $exitMovementDetail->product_id = $bale->product_id;
                                            $exitMovementDetail->qty = $bale->qty;
                                            $exitMovementDetail->bale_id = $bale->id;
                                            if ($exitMovementDetail->create()) {
                                                $entryMovementDetail = new MovementDetails();
                                                $entryMovementDetail->movement_id = $entryMovement->id;
                                                $entryMovementDetail->product_id = $bale->product_id;
                                                $entryMovementDetail->qty = $bale->qty;
                                                $entryMovementDetail->bale_id = $bale->id;
                                                if ($entryMovementDetail->create()) {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('Paca de producto terminado agregada exitosamente.');
                                                    $tx->commit();
                                                } else {
                                                    $this->content['message'] = Message::error('Error al registrar entrada de paca.');
                                                }
                                            } else {
                                                $this->content['message'] = Message::error('Error al registrar salida de paca.');
                                            }
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('No se ha encontado la paca seleccionada.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('El producto está inactivo.');
                                }
                            } else {
                                $this->content['message'] = Message::error('No se ha encontrado la paca.');
                            }
                        }
                    } elseif (isset($request['productId']) && is_numeric($request['productId']) && isset($request['qty']) && is_numeric($request['qty'])) {
                        $product = Products::findFirst($request['productId']);
                        if ($product && $product->active) {
                            $line = Lines::findFirst($product->line_id);
                            if ($line->category_id == 5) {
                                $sql = "SELECT s1.product_id, s1.product_name, SUM(s1.qty) AS qty
                                        FROM (
                                            SELECT md.id, md.product_id, p.name AS product_name, CASE WHEN m.type = 1 THEN md.qty WHEN m.type = 2 THEN (md.qty * -1) END AS qty, m.date
                                            FROM wms_movement_details AS md
                                            INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                            INNER JOIN wms_products AS p ON p.id = md.product_id
                                            INNER JOIN wms_lines AS l ON l.id = p.line_id
                                            WHERE l.category_id = 5 AND m.status = 1 AND p.id = $product->id AND p.active AND m.storage_id = $exitMovement->storage_id
                                            ORDER BY date ASC
                                        ) AS s1
                                        GROUP BY product_id, product_name
                                        ORDER BY product_name ASC;";
                                $laminateStock = $this->db->query($sql)->fetch();
                                if ($laminateStock) {
                                    if ($laminateStock['qty'] > $request['qty']) {
                                        $exitMovementDetail = new MovementDetails();
                                        $exitMovementDetail->movement_id = $exitMovement->id;
                                        $exitMovementDetail->product_id = $product->id;
                                        $exitMovementDetail->qty = $request['qty'];
                                        if ($exitMovementDetail->create()) {
                                            $entryMovementDetail = new MovementDetails();
                                            $entryMovementDetail->movement_id = $entryMovement->id;
                                            $entryMovementDetail->product_id = $product->id;
                                            $entryMovementDetail->qty = $request['qty'];
                                            if ($entryMovementDetail->create()) {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('Laminado agregado exitosamente.');
                                                $tx->commit();
                                            } else {
                                                $this->content['message'] = Message::error('Error al registrar entrada de laminado.');
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('Error al registrar salida de laminado.');
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('No se cuenta con la cantidad solicitada.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('No se ha encontrado el laminado en el almacén de origen.');
                                }
                            }
                            elseif ($line->category_id == 14) {
                                $sql = "SELECT s1.product_id, s1.product_name, SUM(s1.qty) AS qty
                                        FROM (
                                            SELECT md.id, md.product_id, p.name AS product_name, CASE WHEN m.type = 1 THEN md.qty WHEN m.type = 2 THEN (md.qty * -1) END AS qty, m.date
                                            FROM wms_movement_details AS md
                                            INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                            INNER JOIN wms_products AS p ON p.id = md.product_id
                                            INNER JOIN wms_lines AS l ON l.id = p.line_id
                                            WHERE l.category_id = 14 AND m.status = 1 AND p.id = $product->id AND p.active AND m.storage_id = $exitMovement->storage_id
                                            ORDER BY date ASC
                                        ) AS s1
                                        GROUP BY product_id, product_name
                                        ORDER BY product_name ASC;";
                                $rawMaterialStock = $this->db->query($sql)->fetch();
                                if ($rawMaterialStock) {
                                    if ($rawMaterialStock['qty'] > $request['qty']) {
                                        $exitMovementDetail = new MovementDetails();
                                        $exitMovementDetail->movement_id = $exitMovement->id;
                                        $exitMovementDetail->product_id = $product->id;
                                        $exitMovementDetail->qty = $request['qty'];
                                        if ($exitMovementDetail->create()) {
                                            $entryMovementDetail = new MovementDetails();
                                            $entryMovementDetail->movement_id = $entryMovement->id;
                                            $entryMovementDetail->product_id = $product->id;
                                            $entryMovementDetail->qty = $request['qty'];
                                            if ($entryMovementDetail->create()) {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('Materia prima agregada exitosamente.');
                                                $tx->commit();
                                            } else {
                                                $this->content['message'] = Message::error('Error al registrar entrada de materia prima.');
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('Error al registrar salida de materia prima.');
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('No se cuenta con la cantidad solicitada.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('No se ha encontrado la materia prima en el almacén de origen.');
                                }
                            }
                            elseif ($line->category_id == 13) {
                                $sql = "SELECT s1.product_id, s1.product_name, SUM(s1.qty) AS qty
                                        FROM (
                                            SELECT md.id, md.product_id, p.name AS product_name, CASE WHEN m.type = 1 THEN md.qty WHEN m.type = 2 THEN (md.qty * -1) END AS qty, m.date
                                            FROM wms_movement_details AS md
                                            INNER JOIN wms_movements AS m ON m.id = md.movement_id
                                            INNER JOIN wms_products AS p ON p.id = md.product_id
                                            INNER JOIN wms_lines AS l ON l.id = p.line_id
                                            WHERE l.category_id = 13 AND m.status = 1 AND p.id = $product->id AND p.active AND m.storage_id = $exitMovement->storage_id
                                            ORDER BY date ASC
                                        ) AS s1
                                        GROUP BY product_id, product_name
                                        ORDER BY product_name ASC;";
                                $inBulkStock = $this->db->query($sql)->fetch();
                                if ($inBulkStock) {
                                    if ($inBulkStock['qty'] > $request['qty']) {
                                        $exitMovementDetail = new MovementDetails();
                                        $exitMovementDetail->movement_id = $exitMovement->id;
                                        $exitMovementDetail->product_id = $product->id;
                                        $exitMovementDetail->qty = $request['qty'];
                                        if ($exitMovementDetail->create()) {
                                            $entryMovementDetail = new MovementDetails();
                                            $entryMovementDetail->movement_id = $entryMovement->id;
                                            $entryMovementDetail->product_id = $product->id;
                                            $entryMovementDetail->qty = $request['qty'];
                                            if ($entryMovementDetail->create()) {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('Fibra abierta agregada exitosamente.');
                                                $tx->commit();
                                            } else {
                                                $this->content['message'] = Message::error('Error al registrar entrada de fibra abierta.');
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('Error al registrar salida de fibra abierta.');
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('No se cuenta con la cantidad solicitada.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('No se ha encontrado el laminado en el almacén de origen.');
                                }
                            }
                        } else {
                            $this->content['message'] = Message::error('Producto no encontrado o inactivo.');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado el traspaso de sucursal.');
                }
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id de traspaso de sucursal válido.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function inBulkTransaction ($transactionId)
    {
        if (is_numeric($transactionId)) {
            $sql = "SELECT md.id, md.product_id, p.name AS product, md.qty,l.code as line_code,c.code as category_code, concat(c.code,'-',l.code,'-',p.name) as name_product
                    FROM wms_movement_details AS md
                    INNER JOIN wms_movements AS m ON m.id = md.movement_id
                    INNER JOIN wms_products AS p ON p.id = md.product_id
                    INNER JOIN wms_lines AS l ON l.id = p.line_id
                    INNER JOIN wms_categories AS c ON c.id = l.category_id
                    WHERE l.category_id = 13
                    AND m.type = 2
                    AND m.transaction_id = $transactionId
                    ORDER BY md.id ASC;";
            $laminates = $this->db->query($sql)->fetchAll();
            $this->content['inbulks'] = $laminates;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No se ha recibido un id de transacción válido.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function deleteBranchTransferBale ($branchTransferId, $baleId)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                if (is_numeric($branchTransferId) && is_numeric($baleId)) {
                    $branchTransfer = BranchTransfers::findFirst($branchTransferId);
                    if ($branchTransfer) {
                        $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $branchTransfer->transaction_id");
                        $entryMovementDetail = MovementDetails::findFirst("movement_id = $entryMovement->id AND bale_id = $baleId");
                        $entryMovementDetail->setTransaction($tx);
                        if ($entryMovementDetail->delete()) {
                            $exitMovement = Movements::findFirst("type = 2 AND transaction_id = $branchTransfer->transaction_id");
                            $exitMovementDetail = MovementDetails::findFirst("movement_id = $exitMovement->id AND bale_id = $baleId");
                            $exitMovementDetail->setTransaction($tx);
                            if ($exitMovementDetail->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('Paca de producto terminado eliminada exitosamente.');
                                $tx->commit();
                            }
                        } else {
                            $this->content['message'] = Message::error('Error al eliminar traspaso de sucursal.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el traspaso de sucursal.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se han recibido id de traspaso e id de paca válidos.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function deleteBranchTransferLaminate ($branchTransferId, $laminateId)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                if (is_numeric($branchTransferId) && is_numeric($laminateId)) {
                    $branchTransfer = BranchTransfers::findFirst($branchTransferId);
                    if ($branchTransfer) {
                        $exitMovementDetail = MovementDetails::findFirst($laminateId);
                        if ($exitMovementDetail->delete()) {
                            $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $branchTransfer->transaction_id");
                            $entryMovementDetail = MovementDetails::findFirst("movement_id = $entryMovement->id AND product_id = $exitMovementDetail->product_id AND qty = $exitMovementDetail->qty");
                            if ($entryMovementDetail->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('Laminado eliminado exitosamente.');
                                $tx->commit();
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el traspaso de sucursal.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se han recibido id de traspaso e id de laminado válidos.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function deleteBranchTransferinBulk ($branchTransferId, $inBulkId)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                if (is_numeric($branchTransferId) && is_numeric($inBulkId)) {
                    $branchTransfer = BranchTransfers::findFirst($branchTransferId);
                    if ($branchTransfer) {
                        $exitMovementDetail = MovementDetails::findFirst($inBulkId);
                        if ($exitMovementDetail->delete()) {
                            $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $branchTransfer->transaction_id");
                            $entryMovementDetail = MovementDetails::findFirst("movement_id = $entryMovement->id AND product_id = $exitMovementDetail->product_id AND qty = $exitMovementDetail->qty");
                            if ($entryMovementDetail->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('Fibra Abierta eliminada exitosamente.');
                                $tx->commit();
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el traspaso de sucursal.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se han recibido id de traspaso e id de laminado válidos.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function deleteBranchTransferRawMaterial ($branchTransferId, $rawMaterialId)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                if (is_numeric($branchTransferId) && is_numeric($rawMaterialId)) {
                    $branchTransfer = BranchTransfers::findFirst($branchTransferId);
                    if ($branchTransfer) {
                        $exitMovementDetail = MovementDetails::findFirst($rawMaterialId);
                        if ($exitMovementDetail->delete()) {
                            $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $branchTransfer->transaction_id");
                            $entryMovementDetail = MovementDetails::findFirst("movement_id = $entryMovement->id AND product_id = $exitMovementDetail->product_id AND qty = $exitMovementDetail->qty");
                            if ($entryMovementDetail->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('Materia prima eliminada exitosamente.');
                                $tx->commit();
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el traspaso de sucursal.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se han recibido id de traspaso e id de materia prima válidos.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3)
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
