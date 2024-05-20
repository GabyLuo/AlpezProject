<?php

use Phalcon\Mvc\Controller;

class OperatorsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getOperators ($pt = 0)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $validUser = Auth::getUserData($this->config);
            $actualAccount = Auth::getUserAccount($validUser->id);
            $content['operators'] = Operators::find("account_id = $actualAccount order by name asc");
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getOperator ($id)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $validUser = Auth::getUserData($this->config);
            $actualAccount = Auth::getUserAccount($validUser->id);
            $content['operator'] = Operators::findFirst("id = $id AND account_id = $actualAccount");
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getOptions () {
        $validUser = Auth::getUserData($this->config);
        $actualAccount = Auth::getUserAccount($validUser->id);
        $sql = "SELECT id AS value, name AS label
                FROM wms_operators
                WHERE account_id = $actualAccount
                ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                $validUser = Auth::getUserData($this->config);
                if ($validUser) {
                    $actualAccount = Auth::getUserAccount($validUser->id);
                    if ($actualAccount) {
                        $operator = new Operators();
                        $operator->setTransaction($tx);
                        $operator->account_id = $actualAccount;
                        $operator->name = strtoupper($request['name']);

                        if ($operator->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El operador ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($operator);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el operador.');
                            $tx->rollback();
                        }
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $request = $this->request->getPut();
                
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualOperator = Operators::findFirst("id = $id AND account_id = $actualAccount");
                
                if ($actualOperator) {
                    $actualOperator->setTransaction($tx);
                    $actualOperator->name = strtoupper($request['name']);

                    if ($actualOperator->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El operador ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($actualOperator);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el operador.');
                        // $tx->rollback();
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualOperator = Operators::findFirst("id = $id AND account_id = $actualAccount");

                if ($actualOperator) {
                    $actualOperator->setTransaction($tx);

                    if ($actualOperator->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El operador ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['errors'] = Helpers::getErrors($actualOperator);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el operador.');
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El operador no existe.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3)
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
