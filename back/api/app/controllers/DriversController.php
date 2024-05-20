<?php

use Phalcon\Mvc\Controller;

class DriversController extends Controller
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
            try{
                $sql = "SELECT postal_code FROM sys_postal_code WHERE id = ".$content['driver']->postal_code_id;
                $content['postal_code'] = $this->db->query($sql)->fetch()['postal_code'];
            } catch (Exception $e){
                $content['postal_code'] = '';
            }

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
                FROM wms_drivers
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
                        $driver = new Drivers();
                        $driver->setTransaction($tx);
                        $driver->account_id = $actualAccount;
                        $driver->name = strtoupper($request['name']);
                        $driver->rfc = strtoupper($request['rfc']);
                        $driver->license = strtoupper($request['license']);
                        // $driver->street = strtoupper($request['street']);
                        // $driver->between_street = strtoupper($request['between_street']);
                        // $driver->indoor_number = strtoupper($request['indoor_number']);
                        // $driver->outdoor_number = strtoupper($request['outdoor_number']);
                        // $driver->postal_code_id = $request['postal_code_id'];
                        // $driver->suburb_id = $request['suburb_id'];

                        if ($driver->create()) {
                            $this->content['result'] = true;
                            $this->content['id'] = $driver->id;
                            $this->content['message'] = Message::success('El chofer ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($driver);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el chofer.');
                            $tx->rollback();
                        }
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
                if (isset($this->content['errors']['code']) && $this->content['errors']['code'] == 23505) {
                    $this->content['message'] = Message::error('RFC o Licencia registrados con anterioridad.');
                }
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

                $actualDriver = Drivers::findFirst("id = $id AND account_id = $actualAccount");
                
                if ($actualDriver) {
                    $actualDriver->setTransaction($tx);
                    $actualDriver->name = strtoupper($request['name']);
                    $actualDriver->rfc = strtoupper($request['rfc']);
                    $actualDriver->license = strtoupper($request['license']);
                    $actualDriver->active = $request['active'];
                    // $actualDriver->street = strtoupper($request['street']);
                    // $actualDriver->between_street = strtoupper($request['between_street']);
                    // $actualDriver->indoor_number = strtoupper($request['indoor_number']);
                    // $actualDriver->outdoor_number = strtoupper($request['outdoor_number']);
                    // $actualDriver->postal_code_id = $request['postal_code_id'];
                    // $actualDriver->suburb_id = $request['suburb_id'];

                    if ($actualDriver->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El chofer ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($actualDriver);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el chofer.');
                        // $tx->rollback();
                    }
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
                if (isset($this->content['errors']['code']) && $this->content['errors']['code'] == 23505) {
                    $this->content['message'] = Message::error('RFC o Licencia registrados con anterioridad.');
                }
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

                $actualDriver = Drivers::findFirst("id = $id AND account_id = $actualAccount");

                if ($actualDriver) {
                    $actualDriver->setTransaction($tx);

                    if ($actualDriver->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El chofer ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['errors'] = Helpers::getErrors($actualDriver);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el chofer.');
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El chofer no existe.');
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
