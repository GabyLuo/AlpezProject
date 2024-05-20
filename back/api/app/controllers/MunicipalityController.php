<?php

use Phalcon\Mvc\Controller;

class MunicipalityController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getMunicipalitys ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT m.id, m.nombre AS municipality_name, s.nombre AS state_name
            FROM log_municipality AS m 
            INNER JOIN log_state AS s ON m.estado_id = s.id
            ORDER BY s.nombre ASC;";

            $data = $this->db->query($sql)->fetchAll();
            $this->content['municipalitys'] = $data;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getMunicipality ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT m.id, m.nombre AS name, s.nombre AS state_name, s.id AS state_id
            FROM log_municipality AS m 
            INNER JOIN log_state AS s ON m.estado_id = s.id
            WHERE m.id = $id
            ORDER BY s.nombre ASC;";

            $data = $this->db->query($sql)->fetch();
            $this->content['municipality'] = $data;
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

                $name = $request['name'];
                $state = strtoupper($request['state_id']);

                $sql = "SELECT id FROM log_municipality
                WHERE nombre = '$name' AND estado_id = '$state'
                ORDER BY nombre ASC;";

                $actualMunicipalitys = $this->db->query($sql)->fetch();
                if ($actualMunicipalitys) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado una Ciudad con el mismo nombre.');
                } else {
                    $municipality = new Municipalitys();
                    $municipality->setTransaction($tx);
                    $municipality->nombre = strtoupper($request['name']);
                    $municipality->estado_id = $request['state_id'];
                    $municipality->account_id = $actualAccount;
                    if ($municipality->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La Ciudad ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($municipality);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la Ciudad.');
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
                $municipality = Municipalitys::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualMunicipalitys = Municipalitys::findFirst("nombre = '".$request['name']."' AND id <> '".$id."' AND estado_id = '".$request['state_id']."'");
                if ($actualMunicipalitys) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado una Ciudad con el mismo nombre.');
                } else {
                    if ($municipality) {
                        $municipality->setTransaction($tx);
                        $municipality->nombre = strtoupper($request['name']);
                        $municipality->estado_id = $request['state_id'];
                        $municipality->account_id = $actualAccount;

                        if ($municipality->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La Ciudad ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($municipality);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la Ciudad.');
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

                $municipality = Municipalitys::findFirst($id);

                if ($municipality) {
                    $municipality->setTransaction($tx);

                    if ($municipality->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La Ciudad ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($municipality);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la Ciudad.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La Ciudad no existe.');
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
