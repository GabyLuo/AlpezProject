<?php

use Phalcon\Mvc\Controller;

class TransactionsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getTransactions ($pt = 0)
    {
        $content = $this->content;
        $sql = "SELECT t.id, om.id AS origin_movement_id, ob.id AS origin_branch_office_id, ob.name AS origin_branch_office_name, os.id AS origin_storage_id, os.name AS origin_storage_name, dm.id AS destination_movement_id, db.id AS destination_branch_office_id, db.name AS destination_branch_office_name, dm.status, dm.created AS date, ds.id AS destination_storage_id, ds.name AS destination_storage_name
                FROM wms_transactions AS t
                INNER JOIN wms_movements AS om
                ON om.transaction_id = t.id AND om.type = 2
                INNER JOIN wms_storages AS os
                ON os.id = om.storage_id
                INNER JOIN wms_branch_offices AS ob
                ON ob.id = os.branch_office_id
                INNER JOIN wms_movements AS dm
                ON dm.transaction_id = t.id AND dm.type = 1
                INNER JOIN wms_storages AS ds
                ON ds.id = dm.storage_id
                INNER JOIN wms_branch_offices AS db
                ON db.id = ds.branch_office_id;";
        $data = $this->db->query($sql);
        //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $content['transactions'] = $data->fetchAll();
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getTransaction ($id)
    {
        $content = $this->content;
        $transaction = null;
        if (is_numeric($id)) {
            $sql = "SELECT t.id, om.id AS origin_movement_id, ob.id AS origin_branch_office_id, ob.name AS origin_branch_office_name, os.id AS origin_storage_id, os.name AS origin_storage_name, dm.id AS destination_movement_id, db.id AS destination_branch_office_id, db.name AS destination_branch_office_name, dm.status, dm.created AS date, ds.id AS destination_storage_id, ds.name AS destination_storage_name
                    FROM wms_transactions AS t
                    INNER JOIN wms_movements AS om
                    ON om.transaction_id = t.id AND om.type = 2
                    INNER JOIN wms_storages AS os
                    ON os.id = om.storage_id
                    INNER JOIN wms_branch_offices AS ob
                    ON ob.id = os.branch_office_id
                    INNER JOIN wms_movements AS dm
                    ON dm.transaction_id = t.id AND dm.type = 1
                    INNER JOIN wms_storages AS ds
                    ON ds.id = dm.storage_id
                    INNER JOIN wms_branch_offices AS db
                    ON db.id = ds.branch_office_id
                    WHERE t.id = $id;";
            
            $data = $this->db->query($sql);
            //$data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $transaction = $data->fetch();
        }
        $content['transaction'] = $transaction;
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function execute ($id)
    {
        if ($this->userHasPermissionToExecute()) {
            if (is_numeric($id)) {
                try {
                    $tx = $this->transactions->get();

                    $exitMovement = Movements::findFirst("type = 2 AND transaction_id = $id");
                    $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $id");
                    if ($exitMovement && $entryMovement) {
                        $errorMessage = "";
                        $exitMovementDetails = MovementDetails::find("movement_id = $exitMovement->id");
                        if (count($exitMovementDetails) == 0) {
                            $this->content['message'] = Message::error("No se puede ejecutar el traspaso ya que no cuenta con pacas de producto terminado seleccionadas");
                        } else {
                            $inactiveProducts = [];
                            foreach ($exitMovementDetails as $detail) {
                                $product = Products::findFirst($detail->product_id);
                                if (!$product->active) {
                                    array_push($inactiveProducts, $product->name);
                                }
                            }
                            if (count($inactiveProducts) == 0) {
                                foreach ($exitMovementDetails as $detail) {
                                    if (is_null($detail->bale_id)) {
                                        $sql = "SELECT s1.product_id, s1.product_name, SUM(s1.qty) AS qty
                                                FROM (
                                                    SELECT md.id, md.product_id, p.name AS product_name, CASE WHEN m.type = 1 THEN md.qty WHEN m.type = 2 THEN (md.qty * -1) END AS qty
                                                    FROM wms_movement_details AS md
                                                    INNER JOIN wms_movements AS m
                                                    ON m.id = md.movement_id
                                                    INNER JOIN wms_products AS p
                                                    ON p.id = md.product_id
                                                    INNER JOIN wms_lines AS l
                                                    ON l.id = p.line_id
                                                    WHERE m.status = 1
                                                    AND m.storage_id = $exitMovement->storage_id
                                                    AND md.product_id = $detail->product_id
                                                    ORDER BY date ASC
                                                ) AS s1
                                                GROUP BY product_id, product_name;";
                                        $productStock = $this->db->query($sql)->fetch();
                                        if (!$productStock || is_null($productStock) || $productStock['qty'] < $detail->qty) {
                                            $product = Products::findFirst($detail->product_id);
                                            if (strlen($errorMessage) == 0) {
                                                $errorMessage = "No se puede ejecutar el traspaso ya que no se tiene el stock suficiente de los siguientes laminados: $product->name";
                                            } else {
                                                $errorMessage .= ", $product->name";
                                            }
                                        }
                                    } else {
                                        $sql = "SELECT md.id, m.date
                                                FROM wms_movement_details AS md
                                                INNER JOIN wms_movements AS m
                                                ON m.id = md.movement_id
                                                WHERE m.type = 1
                                                AND m.status = 1
                                                AND m.storage_id = $exitMovement->storage_id
                                                AND md.bale_id = $detail->bale_id
                                                ORDER BY date DESC
                                                LIMIT 1;";
                                        $baleEntryMovement = $this->db->query($sql)->fetch();
                                        if ($baleEntryMovement) {
                                            $sql = "SELECT value, product_id, qty, date
                                                    FROM (
                                                        SELECT md.bale_id AS value, md.product_id, md.qty, m.date
                                                        FROM wms_movement_details AS md
                                                        INNER JOIN wms_movements AS m
                                                        ON m.id = md.movement_id
                                                        WHERE m.status = 1
                                                        AND m.type = 2
                                                        AND md.bale_id = $detail->bale_id
                                                        AND m.storage_id = $exitMovement->storage_id
                                                        AND m.date >= '".$baleEntryMovement['date']."'
                                                    ) AS sub
                                                    ORDER BY date DESC
                                                    LIMIT 1;";
                                            $baleExitMovement = $this->db->query($sql)->fetch();
                                            if ($baleExitMovement) {
                                                if (strlen($errorMessage) == 0) {
                                                    $errorMessage = "No se puede ejecutar el traspaso ya que las siguientes pacas ya no están disponibles: $detail->bale_id";
                                                } else {
                                                    $errorMessage .= ", $detail->bale_id";
                                                }
                                            }
                                        } else {
                                            $errorMessage = "No se puede ejecutar el traspaso ya que no se ha encontrado la paca $detail->bale_id";
                                        }
                                    }
                                }
                            } else {
                                $errorMessage = 'Los siguientes productos se encuentra inactivos: '.implode(', ', $inactiveProducts).'.';
                            }
                            if (strlen($errorMessage) > 0) {
                                $this->content['message'] = Message::error($errorMessage);
                            } else {
                                $exitMovement->status = 1;
                                $exitMovement->date = date('Y-m-d H:i:s');
                                if ($exitMovement->update()) {
                                    $entryMovement->status = 1;
                                    $entryMovement->date = date('Y-m-d H:i:s');
                                    if ($entryMovement->update()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('El traspaso ha sido ejecutado correctamente.');
                                        $tx->commit();
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($lot);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el traspaso.');
                                    }
                                } else {
                                    $this->content['error'] = Helpers::getErrors($lot);
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el traspaso.');
                                }
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('No se han encontrado los movimientos de entrada y salida.');
                    }
                } catch (Exception $e) {
                    $this->content['errors'] = Message::exception($e);
                }
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermissionToExecute ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 4 OR role_id = 5 OR role_id = 6 OR role_id = 7 OR role_id = 8 OR role_id = 9 OR role_id = 10)
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