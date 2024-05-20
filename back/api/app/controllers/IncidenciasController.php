<?php

use Phalcon\Mvc\Controller;

class IncidenciasController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getIncidencias()
    {
        if ($this->userHasPermission()) {
            $this->content['incidencias'] = Incidencias::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getIncidencia ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['incidencia'] = Incidencias::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM hrs_incidencia_type ORDER BY name ASC;";
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

                $actualCode = Incidencias::findFirst("code = '".$request['code']."'");
                $actualName = Incidencias::findFirst("name = '".$request['name']."'");
                if($actualCode){
                    $this->content['message'] = Message::success('Ya se encuentra registrado una incidencia con el mismo codigo.');
                    }else{
                        if ($actualName) {
                            $this->content['message'] = Message::success('Ya se encuentra registrado una incidencia con el mismo nombre.');
                        } else {
                            $incidencia = new Incidencias();
                            $incidencia->setTransaction($tx);
                            $incidencia->code = strtoupper($request['code']);
                            $incidencia->name = strtoupper($request['name']);
                            $incidencia->account_id = $actualAccount;

                            if ($incidencia->create()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La incidencia ha sido creada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($incidencia);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la incidencia.');
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

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $incidencia = Incidencias::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualName = Incidencias::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                $actualCode = Incidencias::findFirst("code= '".$request['code']."' AND id <> '".$id."'");
                if($actualCode){
                    $this->content['message'] = Message::success('Ya se encuentra registrada una incidencia con el mismo codigo.');
                }else{
                    if ($actualName) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una incidencia con el mismo nombre.');
                    } else {
                        if ($incidencia) {
                            $incidencia->setTransaction($tx);
                            $incidencia->code = strtoupper($request['code']);
                            $incidencia->name = strtoupper($request['name']);
                            $incidencia->account_id = $actualAccount;

                            if ($incidencia->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La incidencia ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($incidencia);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la incidencia.');
                                $tx->rollback();
                            }
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

                $incidencias = Incidencias::findFirst($id);

                if ($incidencias) {
                    $incidencias->setTransaction($tx);

                    if ($incidencias->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La incidencia ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($incidencias);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la incidencia.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La incidencia no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 6 OR role_id = 7)
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
