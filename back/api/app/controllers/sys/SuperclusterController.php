<?php

use Phalcon\Mvc\Controller;

class SuperclusterController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getAll () {
        if ($this->userHasPermission()) {
            $sql = "SELECT sys_supercluster.id, sys_supercluster.code, sys_supercluster.name
                FROM sys_supercluster";
            $data = $this->db->query($sql)->fetchAll();
            $this->content['supercluster'] = $data;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getById ($id) {
        if ($this->userHasPermission()) {
            $sql = "SELECT sys_supercluster.id, sys_supercluster.code, sys_supercluster.name
                FROM sys_supercluster where id = $id";
            $data = $this->db->query($sql)->fetchAll();
            $this->content['supercluster'] = $data;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $validUser = Auth::getUserInfo($this->config);
        $where = "";
        if ($validUser->role_id == 26) {
            $where .= "INNER JOIN sys_users on sys_users.cluster_id = sys_supercluster.id";
        }
        if ($validUser->role_id == 2) {
            $where .= "INNER JOIN wms_branch_offices on wms_branch_offices.cluster_id = sys_supercluster.id
                       INNER JOIN sys_users on sys_users.branch_office_id = wms_branch_offices.id and sys_users.branch_office_id = " . $validUser->branch_office_id;
        }
        $sql = "SELECT distinct sys_supercluster.id as value, sys_supercluster.name as label FROM sys_supercluster $where ORDER BY sys_supercluster.name ASC;";
        $options = $this->db->query($sql)->fetchAll();
        $this->content['options'] = $options;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function create () {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $supercluster = new Supercluster();
                $supercluster->setTransaction($tx);
                $supercluster->name = $request['name'];
                $supercluster->code = $request['code'];
                if ($supercluster->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El cluster ha sido creado.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($supercluster);
                    $this->content['message'] = Message::error($supercluster->getMsgError() ?? 'Ha ocurrido un error al intentar crear el cluster.');
                    $tx->rollback();
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function update ($id) {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                $supercluster = Supercluster::findFirst($id);
                $supercluster->setTransaction($tx);
                $supercluster->name = $request['name'];
                $supercluster->code = $request['code'];
                if ($supercluster->update()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El cluster ha sido modificado.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($supercluster);
                    $this->content['message'] = Message::error($supercluster->getMsgError() ?? 'Ha ocurrido un error al intentar modificar el cluster.');
                    $tx->rollback();
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        $this->response->setJsonContent($this->content);
    }

    public function delete ($id) {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $supercluster = Supercluster::findFirst($id);
                if ($supercluster) {
                    $supercluster->setTransaction($tx);
                    if ($supercluster->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El cluster ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($supercluster);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el cluster .');
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('La dirección no existe.');
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
                    FROM sys_user_roles
                    WHERE ( role_id = 1)
                    AND user_id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
}
