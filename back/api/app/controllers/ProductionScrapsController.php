<?php

use Phalcon\Mvc\Controller;

class ProductionScrapsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getScraps ()
    {
        if ($this->userHasPermission()) {
            $this->content['scraps'] = ProductionScraps::find(['scrap' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getScrap ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['scrap'] = ProductionScraps::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getScrapsByLotId ($lotId)
    {
        if ($this->userHasPermission()) {
            $scraps = [];
            if (!is_null($lotId) && is_numeric($lotId)) {
                $sql = "SELECT s.id, s.process_id, p.name AS process, s.qty, TO_CHAR(s.created, 'dd/mm/yyyy HH24:MI') AS start_date, u.nickname AS created_by, s.process_id AS process_order, s.dryer_number
                        FROM prd_scraps AS s
                        INNER JOIN prd_processes AS p
                        ON p.id = s.process_id
                        INNER JOIN sys_users AS u
                        ON u.id = s.created_by
                        WHERE s.lot_id = $lotId
                        ORDER BY s.id ASC;";
                $scraps = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
            }
            $this->content['scraps'] = $scraps;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getScrapByLotAndProcess ($lotId, $processId, $dryerNumber)
    {
        if ($this->userHasPermission()) {
            if (!is_null($lotId) && is_numeric($lotId) && !is_null($processId) && is_numeric($processId)) {
                $sql = "SELECT s.id, s.process_id, p.name AS process, s.qty, TO_CHAR(s.created, 'dd/mm/yyyy HH24:MI') AS start_date, u.nickname AS created_by, s.process_id AS process_order, s.dryer_number
                        FROM prd_scraps AS s
                        INNER JOIN prd_processes AS p
                        ON p.id = s.process_id
                        INNER JOIN sys_users AS u
                        ON u.id = s.created_by
                        WHERE s.lot_id = $lotId
                        AND s.process_id = $processId";
                if ($processId == 1 && !is_null($dryerNumber) && is_numeric($dryerNumber)) {
                    $sql .= " AND s.dryer_number = $dryerNumber";
                }
                $this->content['scrap'] = $this->db->query($sql)->fetch();
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido la información necesaria.');
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

                if (isset($request['lotId']) && is_numeric($request['lotId']) && isset($request['processId']) && $request['processId'] > 0 && $request['processId'] < 6 && isset($request['qty']) && is_numeric($request['qty'])) {
                    $where = 'lot_id = '.$request['lotId'].' AND process_id = '.$request['processId'];
                    if ($request['processId'] == 1) {
                        $where .= ' AND dryer_number = '.$request['dryerNumber'];
                    }
                    $scrap = ProductionScraps::findFirst($where);
                    if ($scrap) {
                        $this->content['message'] = Message::error('Ya existe un scrap para el lote y proceso seleccionado.');
                    } else {
                        $canCreate = true;
                        $scrap = new ProductionScraps();
                        $scrap->setTransaction($tx);
                        $scrap->lot_id = $request['lotId'];
                        $scrap->qty = $request['qty'];
                        if ($request['processId'] == 1) {
                            if (isset($request['dryerNumber']) && is_numeric($request['dryerNumber'])) {
                                $scrap->process_id = 1;
                                $scrap->dryer_number = $request['dryerNumber'];
                            } else {
                                $this->content['message'] = Message::error('No se ha recibido la secada.');
                                $canCreate = false;
                            }
                        } else {
                            $scrap->process_id = $request['processId'];
                        }

                        if ($canCreate) {
                            if ($scrap->create()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El scrap ha sido registrado correctamente.');
                                $this->content['scrap'] = $scrap;
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($scrap);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el scrap.');
                            }
                        }
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
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();

                    $scrap = ProductionScraps::findFirst($id);

                    $request = $this->request->getPut();

                    if ($scrap) {
                        $scrap->setTransaction($tx);
                        if (isset($request['lotId']) && is_numeric($request['lotId'])) {
                            $scrap->lot_id = $request['lotId'];
                        }
                        if (isset($request['processId']) && is_numeric($request['processId'])) {
                            if (isset($request['processId']) && $request['processId'] == 1) {
                                if (isset($request['dryerNumber']) && is_numeric($request['dryerNumber'])) {
                                    $scrap->process_id = $request['processId'];
                                    $scrap->dryer_number = $request['dryerNumber'];
                                }
                            } else {
                                $scrap->process_id = $request['processId'];
                            }
                        }
                        if (isset($request['qty']) && is_numeric($request['qty'])) {
                            $scrap->qty = $request['qty'];
                        }
                        if ($scrap->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El scrap ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($scrap);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el scrap.');
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el scrap.');
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
            
                    $scrap = ProductionScraps::findFirst($id);
            
                    if ($scrap) {
                        $scrap->setTransaction($tx);

                        if ($scrap->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El scrap ha sido eliminado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($scrap);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el scrap.');
                            }
                        }
                    } else {
                        $this->content['message'] = Message::error('El scrap no existe.');
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
