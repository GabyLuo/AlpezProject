<?php

use Phalcon\Mvc\Controller;

class HandiWorkController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getHandiWorks ()
    {
        if ($this->userHasPermission()) {
            $this->content['gethandiworks'] = HandiWork::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getHandiWork ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['gethandiwork'] = HandiWork::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM wms_handiwork ORDER BY name ASC;";
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

       
                    $actualHandiWork = HandiWork::findFirst("name = '".$request['name']."'");
                    if ($actualHandiWork) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una mano de obra con el mismo nombre.');
                    } else {
                        $HandiWork = new HandiWork();
                        $HandiWork->setTransaction($tx);
                        $HandiWork->name = strtoupper($request['name']);
                        // $HandiWork->time_job = $request['time_job'];
                        $HandiWork->price = $request['price'];

                        if ($HandiWork->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La Mano de Obra ha sido creada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($HandiWork);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la Mano de Obra.');
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

                $HandiWork = HandiWork::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
               

           
                    $actualHandiWork = HandiWork::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualHandiWork) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una Mano de Obra  con el mismo nombre.');
                    } else {
                        if ($HandiWork) {
                            $HandiWork->setTransaction($tx);
                            $HandiWork->name = strtoupper($request['name']);
                            // $HandiWork->time_job = $request['time_job'];
                            $HandiWork->price = $request['price'];

                            if ($HandiWork->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La Mano de Obra ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($HandiWork);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la Mano de Obra.');
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

                $HandiWork = HandiWork::findFirst($id);

                if ($HandiWork) {
                    $HandiWork->setTransaction($tx);

                    if ($HandiWork->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La Mano de Obra a sido Eliminada');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($HandiWork);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la Mano de Obra.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La categoría no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3)
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
