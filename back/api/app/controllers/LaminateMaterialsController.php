<?php

use Phalcon\Mvc\Controller;

class LaminateMaterialsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getLaminateMaterialsByLaminate ($laminateId)
    {
        $content = $this->content;
        $sql = "SELECT md.id, md.bale_id, md.product_id, p.name AS product, md.qty
                FROM wms_movement_details AS md
                INNER JOIN wms_movements AS m
                ON m.id = md.movement_id
                INNER JOIN wms_products AS p
                ON p.id = md.product_id
                INNER JOIN prd_laminates AS l
                ON l.material_movement_id = m.id
                WHERE m.type = 2
                AND l.id = $laminateId
                ORDER BY md.id ASC;";
        $data = $this->db->query($sql);
        // $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $content['materials'] = $data->fetchAll();
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function create ($pt = 0)
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser) {
            $tx = $this->transactions->get();

            $request = $this->request->getPost();

            if (isset($request['laminateId']) && isset($request['baleId']) && is_numeric($request['baleId'])) {
                $laminate = Laminates::findFirst($request['laminateId']);
                if ($laminate) {
                    if ($laminate->status == 'NUEVO') {
                        $bale = Bales::findFirst($request['baleId']);
                        if ($bale) {
                            $product = Products::findFirst($bale->product_id);
                            if ($product && $product->active) {
                                $movement = Movements::findFirst($laminate->material_movement_id);
                                if ($movement) {
                                    $movementDetail = MovementDetails::findFirst("movement_id = $movement->id AND bale_id = $bale->id");
                                    if ($movementDetail) {
                                        $this->content['message'] = Message::error('La paca ya se encuentra agregada al mismo laminado.');
                                    } else {
                                        $sql = "SELECT md.bale_id, md.product_id, md.qty, m.date
                                                FROM wms_movement_details AS md
                                                INNER JOIN wms_movements AS m
                                                ON m.id = md.movement_id
                                                WHERE m.status = 1
                                                AND m.type = 1
                                                AND md.bale_id = $bale->id
                                                AND m.storage_id = $movement->storage_id
                                                ORDER BY m.date DESC
                                                LIMIT 1;";
                                        $baleEntryMovement = $this->db->query($sql)->fetch();
                                        if ($baleEntryMovement) {
                                            $sql = "SELECT bale_id, product_id, qty, date
                                                    FROM (
                                                        SELECT md.bale_id, md.product_id, md.qty, m.date
                                                        FROM wms_movement_details AS md
                                                        INNER JOIN wms_movements AS m
                                                        ON m.id = md.movement_id
                                                        WHERE m.status = 1
                                                        AND m.type = 2
                                                        AND md.bale_id = $bale->id
                                                        AND m.storage_id = $movement->storage_id
                                                        AND m.date >= '".$baleEntryMovement['date']."'
                                                    ) AS sub
                                                    ORDER BY date DESC
                                                    LIMIT 1;";
                                            $baleExitMovement = $this->db->query($sql)->fetch();
                                            if ($baleExitMovement) {
                                                $this->content['message'] = Message::error('La paca seleccionada ya no se encuentra disponible.');
                                            } else {
                                                $movementDetail = new MovementDetails();
                                                $movementDetail->setTransaction($tx);
                                                $movementDetail->movement_id = $movement->id;
                                                $movementDetail->product_id = $bale->product_id;
                                                $movementDetail->bale_id = $bale->id;
                                                $movementDetail->qty = $bale->qty;

                                                if ($movementDetail->create()) {
                                                    $this->content['result'] = true;
                                                    $this->content['message'] = Message::success('Material agregado correctamente al laminado.');
                                                    $tx->commit();
                                                } else {
                                                    $this->content['error'] = Helpers::getErrors($movementDetail);
                                                    $this->content['message'] = Message::error('Ha ocurrido un error al agregar el material al laminado.');
                                                }
                                            }
                                        } else {
                                            $this->content['message'] = Message::error('No se ha encontrado la paca seleccionada.');
                                        }
                                    }
                                } else {
                                    $this->content['message'] = Message::error('No se ha encontrado el movimiento.');
                                }
                            } else {
                                $this->content['message'] = Message::error('El producto está inactivo.');
                            }
                        } else {
                            $this->content['message'] = Message::error('No se ha encontrado la paca seleccionada.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se pueden agregar nuevos materiales al laminado.');
                    }
                } else {
                    $this->content['message'] = Message::error('El laminado no ha sido encontrado.');
                }
            } else {
                $this->content['message'] = Message::error('No se han recibido los datos necesarios para el registro del laminado.');
            }
        }
        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        try {
            $tx = $this->transactions->get();
            if (is_numeric($id)) {
                $movementDetail = MovementDetails::findFirst($id);
                if ($movementDetail) {
                    $movement = Movements::findFirst($movementDetail->movement_id);
                    if ($movement) {
                        if ($movement->status == 0) {
                            $movementDetail->setTransaction($tx);
                            if ($movementDetail->delete()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El material ha sido eliminado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($movementDetail);
                                if ($this->content['error'][1]) {
                                    $this->content['message'] = Message::error($this->content['error'][1]);
                                } else {
                                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el material.');
                                }
                            }
                        } else {
                            $this->content['message'] = Message::error('No se puede eliminar el material.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el movimiento.');
                    }
                } else {
                    $this->content['message'] = Message::error('No se ha encontrado el material.');
                }
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id válido.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
}