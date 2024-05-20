<?php

use Phalcon\Mvc\Controller;

class ChannelsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getChannels ()
    {
        if ($this->userHasPermission()) {
            $this->content['channels'] = Channels::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getChannel ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['channel'] = Channels::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM wms_channels ORDER BY name ASC;";
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

                $actualCategory = Channels::findFirst("code = '".$request['code']."'");

                if ($actualCategory) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Canal con el mismo código.');
                } else {
                    $actualCategory = Channels::findFirst("name = '".$request['name']."'");
                    if ($actualCategory) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un Canal con el mismo nombre.');
                    } else {
                        $category = new Channels();
                        $category->setTransaction($tx);
                        $category->name = strtoupper($request['name']);
                        $category->code = strtoupper($request['code']);
                        $category->account_id = $actualAccount;

                        if ($category->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Canal ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($category);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Canal.');
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

                $category = Channels::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualCategory = Channels::findFirst("code = '".$request['code']."' AND id <> '".$id."'");

                if ($actualCategory) {
                    $this->content['message'] = Message::success('Ya se encuentra registrad un Canal con el mismo código.');
                } else {
                    $actualCategory = Channels::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualCategory) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un Canal con el mismo nombre.');
                    } else {
                        if ($category) {
                            $category->setTransaction($tx);
                            $category->name = strtoupper($request['name']);
                            $category->code = strtoupper($request['code']);
                            $category->account_id = $actualAccount;

                            if ($category->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El Canal ha sido modificado');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($category);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la categoría.');
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

                $category = Channels::findFirst($id);

                if ($category) {
                    $category->setTransaction($tx);

                    if ($category->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El canal ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($category);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Canal.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Canal no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3)
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
