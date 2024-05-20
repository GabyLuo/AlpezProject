<?php

use Phalcon\Mvc\Controller;

class AreasController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getAreas ($pt = 0)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT a.id, a.department_id, a.name, d.name AS department_name
                    FROM hrs_areas AS a
                    INNER JOIN hrs_departments AS d
                    ON a.department_id = d.id";
            
            $data = $this->db->query($sql)->fetchAll();
            $this->content['areas'] = $data;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getArea ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['area'] = Areas::findFirst([$id]);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getOptions () {
        $sql = "SELECT id AS value, name AS label, department_id AS department
                FROM hrs_areas
                ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getOptionsByDepartmentId ($departmentId) {
        $options = [];
        if (is_numeric($departmentId)) {
            $sql = "SELECT a.id AS value, CONCAT(a.name, ' (', d.name, ')') AS label
                    FROM hrs_areas AS a
                    INNER JOIN hrs_departments AS d
                    ON a.department_id = d.id
                    WHERE department_id = $departmentId
                    ORDER BY name ASC;";
            $options = $this->db->query($sql)->fetchAll();
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualArea = Areas::findFirst("department_id = '".$request['department_id']."' AND name = '".$request['name']."'");
                    if ($actualArea) {
                        $this->content['message'] = Message::error('Ya se encuentra registrada un área con el mismo nombre y departamento.');
                    } else {
                        $area = new Areas();
                        $area->setTransaction($tx);
                        $area->name = strtoupper($request['name']);
                        $area->department_id = $request['department_id'];
                        $area->account_id = $actualAccount;

                        if ($area->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El área ha sido creada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($area);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el área.');
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
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualArea = Areas::findFirst("department_id = '".$request['department_id']."' AND name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualArea) {
                        $this->content['message'] = Message::error('Ya se encuentra registrada un área con el mismo nombre y departamento.');
                    } else {
                        $area = Areas::findFirst($id);

                        if ($area) {
                            $area->setTransaction($tx);
                            $area->name = strtoupper($request['name']);
                            $area->department_id = $request['department_id'];
                            $area->account_id = $actualAccount;

                            if ($area->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El área ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($area);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el área.');
                                // $tx->rollback();
                            }
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

                $area = Areas::findFirst($id);

                if ($area) {
                    $area->setTransaction($tx);

                    if ($area->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El área ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($area);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el área.');
                        }
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El área no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 7)
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
