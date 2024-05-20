<?php

use Phalcon\Mvc\Controller;

class DepartmentsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getDepartments()
    {
        if ($this->userHasPermission()) {
            $this->content['departments'] = Departments::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getDepartment ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['department'] = Departments::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM hrs_departments ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['name']
            ];
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

                $actualDepartment = Departments::findFirst("name = '".$request['name']."'");
                    if ($actualDepartment) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un departamento con el mismo nombre.');
                    } else {
                        $department = new Departments();
                        $department->setTransaction($tx);
                        $department->name = strtoupper($request['name']);
                        $department->account_id = $actualAccount;

                        if ($department->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El departamento ha sido creada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($department);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el departamento.');
                            // $tx->rollback();
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

                $department = Departments::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualDepartment = Departments::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualDepartment) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un departamento con el mismo nombre.');
                    } else {
                        if ($department) {
                            $department->setTransaction($tx);
                            $department->name = strtoupper($request['name']);
                            $department->account_id = $actualAccount;

                            if ($department->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El departamento ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($department);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el departamento.');
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

        $this->response->setJsonContent($this->content);
    } 

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $departments = Departments::findFirst($id);

                if ($departments) {
                    $departments->setTransaction($tx);

                    if ($departments->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El departamento ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($departments);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el departamento.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El departamento no existe.');
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
