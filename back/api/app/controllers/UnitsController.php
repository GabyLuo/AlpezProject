<?php

use Phalcon\Mvc\Controller;

class UnitsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getUnits ()
    {
        if ($this->userHasPermission()) {
            $this->content['units'] = Units::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getUnit ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['unit'] = Units::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id AS value, name AS label FROM wms_units ORDER BY label ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                if (isset($request['name']) && isset($request['code'])) {
                    $unit = new Units();
                    $unit->setTransaction($tx);
                    $unit->name = strtoupper($request['name']);
                    $unit->code = strtoupper($request['code']);

                    if ($unit->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La unidad ha sido creada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($unit);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la unidad.');
                        $tx->rollback();
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

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $unit = Units::findFirst($id);

                $request = $this->request->getPut();

                if ($unit) {
                    $unit->setTransaction($tx);
                    $unit->name = strtoupper($request['name']);
                    $unit->code = strtoupper($request['code']);

                    if ($unit->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La unidad ha sido modificada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($unit);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la unidad.');
                        $tx->rollback();
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

                $unit = Units::findFirst($id);

                if ($unit) {
                    $unit->setTransaction($tx);

                    if ($unit->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La unidad ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($unit);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la unidad.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La unidad no existe.');
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
