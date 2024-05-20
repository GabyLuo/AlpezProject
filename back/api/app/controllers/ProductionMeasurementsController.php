<?php

use Phalcon\Mvc\Controller;

class ProductionMeasurementsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getMeasurements ()
    {
        if ($this->userHasPermission()) {
            $this->content['measurements'] = ProductionMeasurements::find(['measurement' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getMeasurement ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['measurement'] = ProductionMeasurements::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getMeasurementsByLotId ($lotId)
    {
        if ($this->userHasPermission()) {
            if (!is_null($lotId) && is_numeric($lotId)) {
                $sql = "SELECT m.id, m.process_id, p.name AS process, m.value, m.measure, m.dryer_number, m.zone_number, TO_CHAR(m.created, 'dd/mm/yyyy HH24:MI') AS start_date, u.nickname AS created_by, CASE WHEN m.process_id = 1 THEN CONCAT(m.process_id, '-', m.dryer_number) ELSE CONCAT(m.process_id) END AS process_order
                        FROM prd_measurements AS m
                        INNER JOIN prd_processes AS p
                        ON p.id = m.process_id
                        INNER JOIN sys_users AS u
                        ON u.id = m.created_by
                        WHERE m.lot_id = $lotId
                        ORDER BY id ASC;";
                $measurements = $this->db->query($sql)->fetchAll();
                $this->content['measurements'] = $measurements;
                $this->content['result'] = true;
                $this->response->setJsonContent($this->content);
            } else {
                $this->content['measurements'] = [];
                $this->content['result'] = false;
                $this->response->setJsonContent($this->content);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
    }

    public function getMeasurementsByLotAndProcess ($lotId, $processId, $dryerNumber)
    {
        if ($this->userHasPermission()) {
            if (!is_null($lotId) && is_numeric($lotId) && !is_null($processId) && is_numeric($processId)) {
                $sql = "SELECT m.id, m.process_id, p.name AS process, m.value, m.measure, m.dryer_number, m.zone_number, TO_CHAR(m.created, 'dd/mm/yyyy HH24:MI') AS start_date, u.nickname AS created_by
                        FROM prd_measurements AS m
                        INNER JOIN prd_processes AS p
                        ON p.id = m.process_id
                        INNER JOIN sys_users AS u
                        ON u.id = m.created_by
                        WHERE m.lot_id = $lotId
                        AND m.process_id = $processId";
                if ($processId == 1 && !is_null($dryerNumber) && is_numeric($dryerNumber)) {
                    $sql .= " AND m.dryer_number = $dryerNumber";
                }
                $sql .= " ORDER BY id ASC;";
                $measurements = $this->db->query($sql)->fetchAll();
                $this->content['measurements'] = $measurements;
                $this->content['result'] = true;
                $this->response->setJsonContent($this->content);
            } else {
                $this->content['measurements'] = [];
                $this->content['result'] = false;
                $this->response->setJsonContent($this->content);
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                if (isset($request['lot_id']) && is_numeric($request['lot_id']) && isset($request['process_id']) && $request['process_id'] > 0 && $request['process_id'] < 6 && isset($request['value']) && is_numeric($request['value']) && isset($request['measure'])) {
                    $canCreate = true;
                    $measurement = new ProductionMeasurements();
                    $measurement->setTransaction($tx);
                    $measurement->lot_id = $request['lot_id'];
                    $measurement->process_id = $request['process_id'];
                    $measurement->value = $request['value'];
                    $measurement->measure = strtoupper($request['measure']);
                    if ($request['process_id'] == 1 && isset($request['dryer_number']) && is_numeric($request['dryer_number'])) {
                        $measurement->dryer_number = $request['dryer_number'];
                    } else {
                        $measurement->dryer_number = null;
                    }
                    if (($request['process_id'] == 2 || $request['process_id'] == 5) && isset($request['zone_number'])) {
                        $measurement->zone_number = strtoupper($request['zone_number']);
                    } else {
                        $measurement->zone_number = null;
                    }

                    if ($canCreate) {
                        if ($measurement->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La medición ha sido creada correctamente.');
                            $this->content['measurement'] = $measurement;
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($measurement);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la medición.');
                            $tx->rollback();
                        }
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::error('El proceso Empaque no puede tener mediciones registradas.');
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
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();

                    $measurement = ProductionMeasurements::findFirst($id);

                    $request = $this->request->getPut();

                    if ($measurement) {
                        $canUpdate = true;
                        $measurement->setTransaction($tx);
                        $measurement->process_id = $request['process_id'];
                        $measurement->value = $request['value'];
                        $measurement->measure = strtoupper($request['measure']);
                        if ($request['process_id'] == 1 && isset($request['dryer_number']) && is_numeric($request['dryer_number'])) {
                            $measurement->dryer_number = $request['dryer_number'];
                        } else {
                            $measurement->dryer_number = null;
                        }
                        if (($request['process_id'] == 2 || $request['process_id'] == 5) && isset($request['zone_number'])) {
                            $measurement->zone_number = strtoupper($request['zone_number']);
                        } else {
                            $measurement->zone_number = null;
                        }
                        if ($canUpdate) {
                            if ($measurement->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La medición ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($measurement);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la medición.');
                                $tx->rollback();
                            }
                        }
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

    public function delete ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();
            
                    $measurement = ProductionMeasurements::findFirst($id);
            
                    if ($measurement) {
                        $measurement->setTransaction($tx);

                        if ($measurement->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La medición ha sido eliminada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($measurement);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la medición.');
                            }
                            // $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('La medición no existe.');
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id válida.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 5 OR role_id = 6 OR role_id = 7)
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
