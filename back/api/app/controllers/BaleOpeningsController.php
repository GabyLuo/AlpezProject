<?php

use Phalcon\Mvc\Controller;

class BaleOpeningsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getBaleOpenings ($pt = 0)
    {
        $content = $this->content;
        $sql = "SELECT bo.id, b.id AS branch_office_id, b.name AS branch_office_name, bm.id AS bale_movement_id, bs.id AS bale_storage_id, bs.name AS bale_storage_name, bo.operator_id, o.name AS operator_name, TO_CHAR(bo.created, 'yyyy/mm/dd') AS date, bo.opened_by, bo.status, ibm.id AS in_bulk_movement_id, ibs.id AS in_bulk_storage_id, ibs.name AS in_bulk_storage_name, bulkmd.qty
                FROM sls_bale_openings AS bo
                INNER JOIN sls_bale_opening_details AS sbod ON sbod.bale_opening_id = bo.id
                INNER JOIN wms_operators AS o ON o.id = bo.operator_id
                INNER JOIN wms_transactions AS t ON t.id = bo.transaction_id
                INNER JOIN wms_movements AS bm ON bm.transaction_id = t.id AND bm.type = 2
                INNER JOIN wms_storages AS bs ON bs.id = bm.storage_id
                INNER JOIN wms_branch_offices AS b ON b.id = bs.branch_office_id
                INNER JOIN wms_movements AS ibm ON ibm.transaction_id = t.id AND ibm.type = 1    
                INNER JOIN wms_storages AS ibs ON ibs.id = ibm.storage_id
                INNER JOIN wms_movement_details AS bulkmd ON bulkmd.id = sbod.entry_movement_detail_id
                ORDER BY bo.id ASC;";
        $data = $this->db->query($sql);
        //$data->setFetchMode('\Phalcon\Db::FETCH_ASSOC');
        $content['baleOpenings'] = $data->fetchAll();
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getBaleOpening ($id)
    {
        $content = $this->content;
        $sql = "SELECT bo.id, b.id AS branch_office_id, b.name AS branch_office_name, bm.id AS bale_movement_id, bs.id AS bale_storage_id, bs.name AS bale_storage_name, bo.operator_id, o.name AS operator_name, TO_CHAR(bo.created, 'yyyy/mm/dd') AS date, bo.opened_by, bo.status, ibm.id AS in_bulk_movement_id, ibs.id AS in_bulk_storage_id, ibs.name AS in_bulk_storage_name
                FROM sls_bale_openings AS bo
                INNER JOIN wms_operators AS o
                ON o.id = bo.operator_id
                INNER JOIN wms_transactions AS t
                ON t.id = bo.transaction_id
                INNER JOIN wms_movements AS bm
                ON bm.transaction_id = t.id AND bm.type = 2
                INNER JOIN wms_storages AS bs
                ON bs.id = bm.storage_id
                INNER JOIN wms_branch_offices AS b
                ON b.id = bs.branch_office_id
                INNER JOIN wms_movements AS ibm
                ON ibm.transaction_id = t.id AND ibm.type = 1
                INNER JOIN wms_storages AS ibs
                ON ibs.id = ibm.storage_id
                AND bo.id = $id;";
        $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $content['baleOpening'] = $data->fetch();
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function create ($pt = 0)
    {
        $tx = $this->transactions->get();

        $request = $this->request->getPost();

        $validUser = Auth::getUserData($this->config);
        $actualAccount = Auth::getUserAccount($validUser->id);

        if (isset($request['branchOffice']) && is_numeric($request['branchOffice']) && isset($request['operator']) && is_numeric($request['operator'])) {
            $originStorage = Storages::findFirst("account_id = $actualAccount AND storage_type_id = 1 AND branch_office_id = ".$request['branchOffice']);
            $destinationStorage = Storages::findFirst("account_id = $actualAccount AND storage_type_id = 2 AND branch_office_id = ".$request['branchOffice']);
            if ($originStorage && $destinationStorage) {
                $transaction = new Transactions();
                $transaction->setTransaction($tx);
                if ($transaction->create()) {
                    $exitMovement = new Movements();
                    $exitMovement->setTransaction($tx);
                    $exitMovement->storage_id = $originStorage->id;
                    $exitMovement->type = 2;
                    $exitMovement->transaction_id = $transaction->id;
                    if ($exitMovement->create()) {
                        $entryMovement = new Movements();
                        $entryMovement->setTransaction($tx);
                        $entryMovement->storage_id = $destinationStorage->id;
                        $entryMovement->type = 1;
                        $entryMovement->transaction_id = $transaction->id;
                        if ($entryMovement->create()) {
                            $baleOpening = new BaleOpenings();
                            $baleOpening->setTransaction($tx);
                            $baleOpening->transaction_id = $transaction->id;
                            $baleOpening->operator_id = $request['operator'];
                            $baleOpening->opened_by = strtoupper($request['openedBy']);
                            if ($baleOpening->create()) {
                                $this->content['result'] = true;
                                $this->content['baleOpening'] = $baleOpening;
                                $this->content['message'] = Message::success('Apertura de paca registrada correctamente.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($baleOpening);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la apertura de paca.');
                                $tx->rollback();
                            }
                        } else {
                            $this->content['error'] = Helpers::getErrors($entryMovement);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la apertura de paca.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['error'] = Helpers::getErrors($exitMovement);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la apertura de paca.');
                        $tx->rollback();
                    }
                } else {
                    $this->content['error'] = Helpers::getErrors($transaction);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la apertura de paca.');
                }
                $this->content['transaction'] = $transaction;
            } else {
                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la apertura de paca.');
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function execute ($id)
    {
        if (is_numeric($id)) {
            try {
                $baleOpening = BaleOpenings::findFirst($id);
                if ($baleOpening) {
                    $tx = $this->transactions->get();
                    $exitMovement = Movements::findFirst("type = 2 AND transaction_id = $baleOpening->transaction_id");
                    $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $baleOpening->transaction_id");
                    if ($exitMovement && $entryMovement) {
                        $errorMessage = "";
                        $exitMovementDetails = MovementDetails::find("movement_id = $exitMovement->id");
                        $entryMovementDetails = MovementDetails::find("movement_id = $entryMovement->id");
                        if (count($exitMovementDetails) > 0) {
                            $inactiveProducts = [];
                            foreach ($exitMovementDetails as $detail) {
                                $product = Products::findFirst($detail->product_id);
                                if (!$product->active) {
                                    array_push($inactiveProducts, $product->name);
                                }
                            }
                            foreach ($entryMovementDetails as $detail) {
                                $product = Products::findFirst($detail->product_id);
                                if (!$product->active) {
                                    array_push($inactiveProducts, $product->name);
                                }
                            }
                            if (count($inactiveProducts) == 0) {
                                foreach ($exitMovementDetails as $detail) {
                                    $sql = "SELECT md.id, m.date
                                            FROM wms_bales AS b
                                            INNER JOIN wms_movement_details AS md
                                            ON md.bale_id = b.id
                                            INNER JOIN wms_movements AS m
                                            ON m.id = md.movement_id
                                            WHERE m.type = 1
                                            AND m.status = 1
                                            AND m.storage_id = $exitMovement->storage_id
                                            AND b.id = $detail->bale_id
                                            ORDER BY date DESC
                                            LIMIT 1;";
                                    $baleEntryMovement = $this->db->query($sql)->fetch();
                                    if ($baleEntryMovement) {
                                        $sql = "SELECT m.date, md.id
                                                FROM wms_bales AS b
                                                INNER JOIN wms_movement_details AS md
                                                ON md.bale_id = b.id
                                                INNER JOIN wms_movements AS m
                                                ON m.id = md.movement_id
                                                WHERE m.type = 2
                                                AND m.status = 1
                                                AND m.storage_id = $exitMovement->storage_id
                                                AND b.id = $detail->bale_id
                                                AND m.date >= '".$baleEntryMovement['date']."'
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
                                        $errorMessage = "No se puede ejecutar el traspaso ya que no se ha encontrado la paca $detail->bale_id en el almacén seleccionado";
                                    }
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
                                            $baleOpening->setTransaction($tx);
                                            $baleOpening->status = 'EJECUTADO';
                                            if ($baleOpening->update()) {
                                                $this->content['result'] = true;
                                                $this->content['message'] = Message::success('El traspaso ha sido ejecutado correctamente.');
                                                $tx->commit();
                                            } else {
                                                $this->content['error'] = Helpers::getErrors($baleOpening);
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el traspaso.');
                                                $tx->rollback();
                                            }
                                        } else {
                                            $this->content['error'] = Helpers::getErrors($entryMovement);
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el traspaso.');
                                            $tx->rollback();
                                        }
                                    } else {
                                        $this->content['error'] = Helpers::getErrors($exitMovement);
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el traspaso.');
                                    }
                                }
                            } else {
                                $this->content['message'] = Message::error('Los siguientes productos se encuentra inactivos: '.implode(', ', $inactiveProducts).'.');
                            }
                        } else {
                            $this->content['message'] = Message::error("No se puede ejecutar el traspaso ya que no cuenta con pacas de producto terminado seleccionadas");
                        }
                    } else {
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar ejecutar el traspaso.');
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }

        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        try {
            $tx = $this->transactions->get();
            if (is_numeric($id)) {
                $baleOpening = BaleOpenings::findFirst($id);
                if ($baleOpening) {
                    $transaction = Transactions::findFirst($baleOpening->transaction_id);
                    if ($transaction) {
                        $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $transaction->id");
                        $exitMovement = Movements::findFirst("type = 2 AND transaction_id = $transaction->id");
                        if ($entryMovement && $exitMovement) {
                            if ($entryMovement->status == 0 && $exitMovement->status == 0) {
                                $entryMovementDetails = MovementDetails::find("movement_id = $entryMovement->id");
                                $exitMovementDetails = MovementDetails::find("movement_id = $exitMovement->id");
                                if (sizeof($entryMovementDetails) == 0 && sizeof($exitMovementDetails) == 0) {
                                    $entryMovement->setTransaction($tx);
                                    if ($entryMovement->delete()) {
                                        $exitMovement->setTransaction($tx);
                                        if ($exitMovement->delete()) {
                                            if ($baleOpening->delete()) {
                                                if ($transaction->delete()) {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('Apertura de paca eliminada.');
                                                    $tx->commit();
                                                } else {
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la apertura de paca.');
                                                }
                                            } else {
                                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la apertura de paca.');
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la apertura de paca.');
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la apertura de paca.');
                                    }
                                } else {
                                    $this->content['message'] = Message::error('La apertura de paca cuenta con detalles registrados.');
                                }
                            } else {
                                $this->content['message'] = Message::error('La apertura de paca ya se encuentra ejecutada.');
                            }
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la apertura de paca.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No ha encontrado la apertura de paca.');
                    }
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
}
