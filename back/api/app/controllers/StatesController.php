<?php

use Phalcon\Mvc\Controller;

class StatesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getStates ()
    {
        if ($this->userHasPermission()) {
            $this->content['states'] = States::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getState ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['state'] = States::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    /*public function getOptions () {
        $sql = "SELECT id, name FROM log_expenses_type ORDER BY name ASC;";
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
    }*/

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualStates = States::findFirst("nombre = '".$request['name']."'");
                if ($actualStates) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Estado con el mismo nombre.');
                } else {
                    $state = new States();
                    $state->setTransaction($tx);
                    $state->nombre = strtoupper($request['name']);
                    $state->account_id = $actualAccount;
                    if ($state->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Estado ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($state);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Estado.');
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
                $state = States::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualStates = States::findFirst("nombre = '".$request['nombre']."' AND id <> '".$id."'");
                if ($actualStates) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Estado con el mismo nombre.');
                } else {
                    if ($state) {
                        $state->setTransaction($tx);
                        $state->nombre = strtoupper($request['nombre']);
                        $state->account_id = $actualAccount;

                        if ($state->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Estado ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($state);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Typo de gasto.');
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

                $state = States::findFirst($id);

                if ($state) {
                    $state->setTransaction($tx);

                    if ($state->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Estado ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($state);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Estado.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Estado no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 7 OR role_id = 8)
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
