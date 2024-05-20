<?php

use Phalcon\Mvc\Controller;

class ShiftsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getShifts()
    {
        if ($this->userHasPermission()) {
            $this->content['shifts'] = Shifts::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getShift ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['shift'] = Shifts::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM hrs_shifts ORDER BY name ASC;";
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

                $actualShift = Shifts::findFirst("name = '".$request['name']."'");
                    if ($actualShift) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un turno con el mismo nombre.');
                    } else {
                        $shift = new Shifts();
                        $shift->setTransaction($tx);
                        $shift->name = strtoupper($request['name']);
                        $shift->account_id = $actualAccount;

                        if ($shift->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El turno ha sido creada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($shift);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el turno.');
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

                $shift = Shifts::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualShift = Shifts::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualShift) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un departamento con el mismo nombre.');
                    } else {
                        if ($shift) {
                            $shift->setTransaction($tx);
                            $shift->name = strtoupper($request['name']);
                            $shift->account_id = $actualAccount;

                            if ($shift->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El departamento ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($shift);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el departamento.');
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

                $shifts = Shifts::findFirst($id);

                if ($shifts) {
                    $shifts->setTransaction($tx);

                    if ($shifts->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El turno ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($shifts);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el turno.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El turno no existe.');
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
                    FROM sys_user
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 7)
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
