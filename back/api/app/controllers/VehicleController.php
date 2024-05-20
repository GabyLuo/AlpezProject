<?php

use Phalcon\Mvc\Controller;

class VehicleController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getVehicles ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT v.id, v.economic_number, v.vehicle_brand, v.vehicle_model, v.year, v.vin, v.license_plate, vt.name AS type_id 
            FROM log_vehicle  AS V 
            INNER JOIN log_vehicle_type AS vt ON v.type_id = vt.id 
            ORDER BY v.id ASC;";

            $vehicles = $this->db->query($sql)->fetchAll();
            $this->content['vehicles'] = $vehicles;
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
            $sql = "SELECT v.*
            FROM log_vehicle  AS V 
            WHERE v.id = $id
            ORDER BY v.id ASC;";

            $vehicles = $this->db->query($sql)->fetch();
            $this->content['vehicle'] = $vehicles;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getVehicleData ($id) {
        if ($this->userHasPermission()) {
            $sql = "SELECT v.vehicle_brand AS brand, v.vehicle_model AS model, vt.name AS type,  v.license_plate AS plate
            FROM log_vehicle  AS V 
            INNER JOIN log_vehicle_type AS vt ON v.type_id = vt.id 
            WHERE v.id = $id
            ORDER BY v.id ASC;";

            $vehicles = $this->db->query($sql)->fetch();
            $this->content['vehicle'] = $vehicles;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, economic_number FROM log_vehicle ORDER BY id ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['economic_number']
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

                $actualVehicle = Vehicle::findFirst("economic_number = '".$request['economic_number']."'");
                if ($actualVehicle) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Vehículo con el mismo numero economico.');
                } else {
                    $vehicle = new Vehicle();
                    $vehicle->setTransaction($tx);
                    $vehicle->economic_number = $request['economic_number'];
                    $vehicle->type_id = $request['type_id'];
                    $vehicle->vehicle_brand = $request['vehicle_brand'];
                    $vehicle->vehicle_model = $request['vehicle_model'];
                    $vehicle->year = $request['year'];
                    $vehicle->license_plate = $request['license_plate'];
                    $vehicle->account_id = $actualAccount;

                    $vehicle->perm_sct = isset($request['perm_sct'])?$request['perm_sct']:null;
                    $vehicle->perm_num_sct = $request['perm_num_sct'];
                    $vehicle->vehicle_config = $request['vehicle_config'];
                    $vehicle->insurance_civil_resp = $request['insurance_civil_resp'];
                    $vehicle->resp_civil_policy = $request['resp_civil_policy'];
                    $vehicle->ambience_insurance = $request['ambience_insurance'];
                    $vehicle->ambience_insurance_policy = $request['ambience_insurance_policy'];
                    $vehicle->has_towing = $request['has_towing']!=''?$request['has_towing']:false;
                    $vehicle->towing_type_id = $request['towing_type_id']!=''?$request['towing_type_id']:null;
                    $vehicle->towing_plate = $request['towing_plate'];

                    if ($vehicle->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Vehículo ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($vehicle);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Vehículo.');
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
                $vehicle = Vehicle::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualVehicle = Vehicle::findFirst("economic_number = '".$request['economic_number']."' AND id <> '".$id."'");
                if ($actualVehicle) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Vehículo con el mismo numero economico.');
                } else {
                    if ($vehicle) {
                        $vehicle->setTransaction($tx);
                        $vehicle->economic_number = $request['economic_number'];
                        $vehicle->type_id = $request['type_id'];
                        $vehicle->vehicle_brand = $request['vehicle_brand'];
                        $vehicle->vehicle_model = $request['vehicle_model'];
                        $vehicle->year = $request['year'];
                        $vehicle->license_plate = $request['license_plate'];
                        $vehicle->account_id = $actualAccount;

                        $vehicle->perm_sct = isset($request['perm_sct'])?$request['perm_sct']:null;
                        $vehicle->perm_num_sct = $request['perm_num_sct'];
                        $vehicle->vehicle_config = $request['vehicle_config'];
                        $vehicle->insurance_civil_resp = $request['insurance_civil_resp'];
                        $vehicle->resp_civil_policy = $request['resp_civil_policy'];
                        $vehicle->ambience_insurance = $request['ambience_insurance'];
                        $vehicle->ambience_insurance_policy = $request['ambience_insurance_policy'];
                        $vehicle->has_towing = $request['has_towing']!=''?$request['has_towing']:false;
                        $vehicle->towing_type_id = $request['towing_type_id']!=''?$request['towing_type_id']:null;
                        $vehicle->towing_plate = $request['towing_plate'];

                        if ($vehicle->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Vehículo ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($vehicle);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Vehículo.');
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

                $vehicle = Vehicle::findFirst($id);

                if ($vehicle) {
                    $vehicle->setTransaction($tx);

                    if ($vehicle->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Vehículo ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($vehicle);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Vehículo.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Vehículo no existe.');
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
