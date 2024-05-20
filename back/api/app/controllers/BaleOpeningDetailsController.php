<?php

use Phalcon\Mvc\Controller;

class BaleOpeningDetailsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getBaleOpeningsDetails ($pt = 0)
    {
        $content = $this->content;
        $sql = "SELECT bod.id, balemd.bale_id, balemd.product_id AS bale_product_id, balep.name AS bale_product_name, bulkmd.product_id AS bulk_product_id, bulkp.name AS bulk_product_name, bulkmd.qty
                FROM sls_bale_opening_details AS bod
                INNER JOIN wms_movement_details AS balemd ON balemd.id = bod.exit_movement_detail_id
                INNER JOIN wms_products AS balep ON balep.id = balemd.product_id
                INNER JOIN wms_movement_details AS bulkmd ON bulkmd.id = bod.entry_movement_detail_id
                INNER JOIN wms_products AS bulkp  ON bulkp.id = bulkmd.product_id;";
        $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $content['details'] = $data->fetchAll();
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getBaleOpeningDetails ($id)
    {
        $content = $this->content;
        $sql = "SELECT bod.id, balemd.bale_id, balemd.product_id AS bale_product_id, balep.name AS bale_product_name, bulkmd.product_id AS bulk_product_id, bulkp.name AS bulk_product_name, bulkmd.qty
                FROM sls_bale_opening_details AS bod
                INNER JOIN wms_movement_details AS balemd
                ON balemd.id = bod.exit_movement_detail_id
                INNER JOIN wms_products AS balep
                ON balep.id = balemd.product_id
                INNER JOIN wms_movement_details AS bulkmd
                ON bulkmd.id = bod.entry_movement_detail_id
                INNER JOIN wms_products AS bulkp
                ON bulkp.id = bulkmd.product_id
                WHERE bod.bale_opening_id = $id;";
        $data = $this->db->query($sql);
       //  $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $content['details'] = $data->fetchAll();
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function create ()
    {
        $tx = $this->transactions->get();

        $request = $this->request->getPost();

        if (isset($request['baleOpeningId']) && is_numeric($request['baleOpeningId']) && isset($request['baleId']) && is_numeric($request['baleId'])&& isset($request['bulkFiberProductId']) && is_numeric($request['bulkFiberProductId'])) {
            $baleOpening = BaleOpenings::findFirst($request['baleOpeningId']);
            if ($baleOpening) {
                $exitMovement = Movements::findFirst("type = 2 AND transaction_id = $baleOpening->transaction_id");
                $entryMovement = Movements::findFirst("type = 1 AND transaction_id = $baleOpening->transaction_id");
                if ($exitMovement && $entryMovement) {
                    $bale = Bales::findFirst($request['baleId']);
                    if ($bale) {
                        $baleProduct = Products::findFirst($bale->product_id);
                        if ($baleProduct && $baleProduct->active) {
                            $bulkFiberProduct = Products::findFirst($request['bulkFiberProductId']);
                            if ($bulkFiberProduct && $bulkFiberProduct->active) {
                                $movementDetail = MovementDetails::findFirst("movement_id = $exitMovement->id AND bale_id = $bale->id");
                                if ($movementDetail) {
                                    $this->content['message'] = Message::error('La paca seleccionada ya se encuentra agregada.');
                                } else {
                                    $sql = "SELECT md.id, m.date
                                            FROM wms_bales AS b
                                            INNER JOIN wms_movement_details AS md
                                            ON md.bale_id = b.id
                                            INNER JOIN wms_movements AS m
                                            ON m.id = md.movement_id
                                            WHERE m.type = 1
                                            AND m.status = 1
                                            AND m.storage_id = $exitMovement->storage_id
                                            AND b.id = $bale->id
                                            ORDER BY date DESC
                                            LIMIT 1;";
                                    $baleEntryMovement = $this->db->query($sql)->fetch();
                                    if ($baleEntryMovement) {
                                        $sql = "SELECT md.id
                                                FROM wms_bales AS b
                                                INNER JOIN wms_movement_details AS md
                                                ON md.bale_id = b.id
                                                INNER JOIN wms_movements AS m
                                                ON m.id = md.movement_id
                                                WHERE m.type = 2
                                                AND m.status = 1
                                                AND m.storage_id = $exitMovement->storage_id
                                                AND b.id = $bale->id
                                                AND m.date >= '".$baleEntryMovement['date']."'
                                                ORDER BY date DESC
                                                LIMIT 1;";
                                        $baleExitMovement = $this->db->query($sql)->fetch();
                                        if ($baleExitMovement) {
                                            $this->content['message'] = Message::error('La paca seleccionada ya no se encuentra disponible.');
                                        } else {
                                            $exitMovementDetail = new MovementDetails();
                                            $exitMovementDetail->movement_id = $exitMovement->id;
                                            $exitMovementDetail->product_id = $bale->product_id;
                                            $exitMovementDetail->qty = $bale->qty;
                                            $exitMovementDetail->bale_id = $bale->id;
                                            if ($exitMovementDetail->create()) {
                                                $entryMovementDetail = new MovementDetails();
                                                $entryMovementDetail->movement_id = $entryMovement->id;
                                                $entryMovementDetail->product_id = $request['bulkFiberProductId'];
                                                $entryMovementDetail->qty = $bale->qty;
                                                $entryMovementDetail->bale_id = null;
                                                if ($entryMovementDetail->create()) {
                                                    $baleOpeningDetail = new BaleOpeningDetails();
                                                    $baleOpeningDetail->bale_opening_id = $baleOpening->id;
                                                    $baleOpeningDetail->exit_movement_detail_id = $exitMovementDetail->id;
                                                    $baleOpeningDetail->entry_movement_detail_id = $entryMovementDetail->id;
                                                    if ($baleOpeningDetail->create()) {
                                                        $this->content['result'] = true;
                                                        $this->content['message'] = Message::success('Paca de producto terminado agregada exitosamente.');
                                                        $tx->commit();
                                                    } else {
                                                        $this->content['message'] = Message::error('Error al registrar detalle de apertura de paca.');
                                                        $tx->rollback();
                                                    }
                                                } else {
                                                    $this->content['message'] = Message::error('Error al registrar entrada de producto.');
                                                    $tx->rollback();
                                                }
                                            } else {
                                                $this->content['message'] = Message::error('Error al registrar salida de paca.');
                                            }
                                        }
                                    } else {
                                        $this->content['message'] = Message::error('No se ha encontrado la paca seleccionada.');
                                    }
                                }
                            } else {
                                $this->content['message'] = Message::error('El producto fibra abierta está inactivo.');
                            }
                        } else {
                            $this->content['message'] = Message::error('El producto de la paca está inactivo.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado la paca seleccionada.');
                    }
                }
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        try {
            $tx = $this->transactions->get();
            if (is_numeric($id)) {
                $baleOpeningDetail = BaleOpeningDetails::findFirst($id);
                if ($baleOpeningDetail) {
                    $exitMovementDetail = MovementDetails::findFirst($baleOpeningDetail->exit_movement_detail_id);
                    $entryMovementDetail = MovementDetails::findFirst($baleOpeningDetail->entry_movement_detail_id);
                    if ($exitMovementDetail && $entryMovementDetail) {
                        $exitMovement = Movements::findFirst($exitMovementDetail->movement_id);
                        $entryMovement = Movements::findFirst($entryMovementDetail->movement_id);
                        if ($exitMovement && $exitMovement->status == 0 && $entryMovement && $entryMovement->status == 0) {
                            if ($baleOpeningDetail->delete()) {
                                if ($entryMovementDetail->delete()) {
                                    if ($exitMovementDetail->delete()) {
                                        $this->content['result'] = true;
                                        $this->content['message'] = Message::success('Detalle eliminado correctamente.');
                                        $tx->commit();
                                    } else {
                                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el detalle.');
                                        $tx->rollback();
                                    }
                                } else {
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el detalle.');
                                    $tx->rollback();
                                }
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el detalle.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se puede eliminar el detalle.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el detalle.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado la apertura de paca.');
                }
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
}
