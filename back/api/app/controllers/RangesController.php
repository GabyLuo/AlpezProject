<?php

use Phalcon\Mvc\Controller;

class RangesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getRanges ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT r.id, r.name, r.distance, s.nombre AS state, m.nombre AS municipality 
            FROM log_ranges AS r 
            INNER JOIN log_state AS s ON r.state = s.id
            INNER JOIN log_municipality AS m ON r.municipality = m.id
            ORDER BY name ASC;";
            $ranges = $this->db->query($sql)->fetchAll();

            $this->content['ranges'] = $ranges;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getRange ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT r.id, r.name, r.distance, s.nombre AS state_name, s.id as state_id, m.nombre AS municipality_name, m.id AS municipality_id 
            FROM log_ranges AS r 
            INNER JOIN log_state AS s ON r.state = s.id
            INNER JOIN log_municipality AS m ON r.municipality = m.id
            WHERE r.id = $id";
            $range = $this->db->query($sql)->fetch();

            $this->content['range'] = $range;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name, distance FROM log_ranges ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['name'],
                'distance' => $type['distance']
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

                $actualRange = Ranges::findFirst("name = '".$request['name']."'");
                if ($actualRange) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un destino con el mismo nombre.');
                } else {
                    $range = new Ranges();
                    $range->setTransaction($tx);
                    $range->name = strtoupper($request['name']);
                    $range->distance = $request['distance'];
                    $range->state = $request['state'];
                    $range->municipality = $request['municipality'];
                    $range->account_id = $actualAccount;
                    if ($range->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El destino ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($range);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el nuevo destino.');
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
                $range = Ranges::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualRange = Ranges::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                if ($actualRange) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un destino con el mismo nombre.');
                } else {
                    if ($range) {
                        $range->setTransaction($tx);
                        $range->name = strtoupper($request['name']);
                        $range->distance = strtoupper($request['distance']);
                        $range->state = $request['state'];
                        $range->municipality = $request['municipality'];
                        $range->account_id = $actualAccount;

                        if ($range->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El destino ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($range);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el destino.');
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

                $range = Ranges::findFirst($id);

                if ($range) {
                    $range->setTransaction($tx);

                    if ($range->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El destino ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($range);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el destino.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El destino no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function getStateOptions () {
        $sql = "SELECT id, nombre AS name FROM log_state ORDER BY name ASC;";
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

    public function getMunicipalityOptions () {
        $sql = "SELECT id, nombre AS name, estado_id AS state_id FROM log_municipality ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['name'],
                'state_id' => $type['state_id']
            ];
        }
        $this->content['options'] = $options;
        $this->content['result'] = true;
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
