<?php

use Phalcon\Mvc\Controller;

class VehicleTypeController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getVehicles ()
    {
        if ($this->userHasPermission()) {
            $this->content['vehicles'] = VehicleType::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getVehicle ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['vehicle'] = VehicleType::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM log_vehicle_type ORDER BY name ASC;";
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

    public function getOptionsTowing () {
        $sql = "SELECT id as value, description as label FROM sys_towing_type ORDER BY description ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $this->content['options'] = $types;
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function getOptionsConfig () {
        $sql = "SELECT id as value, description as label FROM sys_auto_transport ORDER BY description ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $this->content['options'] = $types;
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

                $actualVehicle = VehicleType::findFirst("name = '".$request['name']."'");
                if ($actualVehicle) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Tipo de vehículo con el mismo nombre.');
                } else {
                    $vehicle = new VehicleType();
                    $vehicle->setTransaction($tx);
                    $vehicle->name = strtoupper($request['name']);
                    $vehicle->account_id = $actualAccount;
                    if ($vehicle->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Tipo de vehículo ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($vehicle);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Tipo de vehículo.');
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
                $vehicle = VehicleType::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualVehicle = VehicleType::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                if ($actualVehicle) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Tipo de vehículo con el mismo nombre.');
                } else {
                    if ($vehicle) {
                        $vehicle->setTransaction($tx);
                        $vehicle->name = strtoupper($request['name']);
                        $vehicle->account_id = $actualAccount;

                        if ($vehicle->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Tipo de vehículo ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($vehicle);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Tipo de vehículo.');
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

                $vehicle = VehicleType::findFirst($id);

                if ($vehicle) {
                    $vehicle->setTransaction($tx);

                    if ($vehicle->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Tipo de vehículo ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($vehicle);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Tipo de vehículo.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Tipo de vehículo no existe.');
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
