<?php

use Phalcon\Mvc\Controller;

class TripDestinationsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getDrivers ($pt = 0)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $validUser = Auth::getUserData($this->config);
            $actualAccount = Auth::getUserAccount($validUser->id);
            $content['drivers'] = Drivers::find("account_id = $actualAccount order by name asc");
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getDriver ($id)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $validUser = Auth::getUserData($this->config);
            $actualAccount = Auth::getUserAccount($validUser->id);
            $content['driver'] = Drivers::findFirst("id = $id AND account_id = $actualAccount");
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getByTrip ($id)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $validUser = Auth::getUserData($this->config);
            $sql = "select d.*, p.postal_code,su.suburb,m.municipality,l.location,s.state,to_char(d.date,'DD/MM/YYYY HH:mm:ss') as date,
            s.id as state_id,m.id as municipality_id
            from log_trip_destinations d 
            join sys_postal_code p on p.id = d.postal_code_id
            join sys_state s on s.state_code = p.state_code
            join sys_suburbs su on su.id = d.suburb_id
            join sys_municipality m on m.state_code = p.state_code and m.municipality_code = p.municipality_code
            left join sys_location l on l.state_code = p.state_code and l.location_code = p.location_code
            where trip_id = $id
            order by d.id desc";
            $content['destinations'] = $this->db->query($sql)->fetchAll();
            
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function create ()
    {
        if ($this->userHasPermission()) {
            try {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                $validUser = Auth::getUserData($this->config);
                if ($validUser) {
                    $destination = new TripDestinations();
                    $destination->setTransaction($tx);
                    $destination->trip_id = $request['trip_id'];
                    $destination->street = strtoupper($request['street']);
                    $destination->between_street = strtoupper($request['between_street']);
                    $destination->indoor_number = strtoupper($request['indoor_number']);
                    $destination->outdoor_number = strtoupper($request['outdoor_number']);
                    $destination->postal_code_id = $request['postal_code_id'];
                    $destination->suburb_id = $request['suburb_id'];
                    $destination->distance = $request['distance'];
                    $destination->date = $request['date'];

                    $destination->created_by = $validUser->id;

                    if ($destination->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El destino ha sido agregado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($driver);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar agregar el destino.');
                        $tx->rollback();
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
                $destination = TripDestinations::findFirst("id = $id");
                
                if ($destination) {
                    $destination->setTransaction($tx);
                    $destination->street = strtoupper($request['street']);
                    $destination->between_street = strtoupper($request['between_street']);
                    $destination->indoor_number = strtoupper($request['indoor_number']);
                    $destination->outdoor_number = strtoupper($request['outdoor_number']);
                    $destination->postal_code_id = $request['postal_code_id'];
                    $destination->suburb_id = $request['suburb_id'];
                    $destination->distance = $request['distance'];
                    $destination->date = $request['date'];

                    if ($destination->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El destino ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($destination);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el destino.');
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

                $destination = TripDestinations::findFirst("id = $id");

                if ($destination) {
                    $destination->setTransaction($tx);

                    if ($destination->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Destino ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['errors'] = Helpers::getErrors($destination);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Destino.');
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Destino no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 7 OR role_id = 8)
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
