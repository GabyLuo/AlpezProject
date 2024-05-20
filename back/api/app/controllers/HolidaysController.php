<?php

use Phalcon\Mvc\Controller;

class HolidaysController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getHolidays()
    {
        if ($this->userHasPermission()) {
            $this->content['holidays'] = Holidays::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getHoliday ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['holiday'] = Holidays::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, date, note FROM hrs_holidays ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['date']
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

                $actualHoliday = Holidays::findFirst("date = '".$request['date']."'");
                    if ($actualHoliday) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un día festivo con la misma fecha.');
                    } else {
                        $holiday = new Holidays();
                        $holiday->setTransaction($tx);
                        $holiday->date = $request['date'];
                        $holiday->note = strtoupper($request['note']);
                        $holiday->account_id = $actualAccount;

                        if ($holiday->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El día festivo ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($holiday);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el día festivo.');
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

                $holiday = Holidays::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualHoliday = Holidays::findFirst("date = '".$request['date']."' AND id <> '".$id."'");
                    if ($actualHoliday) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un día festivo con la misma fecha.');
                    } else {
                        if ($holiday) {
                            $holiday->setTransaction($tx);
                            $holiday->date = $request['date'];
                            $holiday->note = strtoupper($request['note']);
                            $holiday->account_id = $actualAccount;

                            if ($holiday->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El día festivo ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($holiday);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el día festivo.');
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

                $holidays = Holidays::findFirst($id);

                if ($holidays) {
                    $holidays->setTransaction($tx);

                    if ($holidays->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El día festivo ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($holidays);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el día festivo.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El día festivo no existe.');
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
