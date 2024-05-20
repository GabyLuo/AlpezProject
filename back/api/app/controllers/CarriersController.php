<?php

use Phalcon\Mvc\Controller;

class CarriersController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getCarriers ()
    {
        if ($this->userHasPermission()) {
            $this->content['carriers'] = Carriers::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getCarrier ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['carrier'] = Carriers::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM wms_carriers ORDER BY name ASC;";
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
                $actualCarrier = Carriers::findFirst("code = '".$request['code']."'");

                if ($actualCarrier) {
                    $this->content['message'] = Message::success('Ya se encuentra registrada una paquetería con el mismo código.');
                } else {
                    $actualCarrier = Carriers::findFirst("name = '".$request['name']."'");
                    if ($actualCarrier) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una paquetería con el mismo nombre.');
                    } else {
                        $carrier = new Carriers();
                        $carrier->setTransaction($tx);
                        $carrier->name = strtoupper($request['name']);
                        $carrier->code = strtoupper($request['code']);
                        $carrier->url = $request['url'] ? $request['url'] : null;

                        if ($carrier->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La Paquetería ha sido creada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($carrier);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la paquetería.');
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

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $carrier = Carriers::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualCarrier = Carriers::findFirst("code = '".$request['code']."' AND id <> '".$id."'");

                if ($actualCarrier) {
                    $this->content['message'] = Message::success('Ya se encuentra registrada una paquetería con el mismo código.');
                } else {
                    $actualCarrier = Carriers::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualCarrier) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una paquetería con el mismo nombre.');
                    } else {
                        if ($carrier) {
                            $carrier->setTransaction($tx);
                            $carrier->name = strtoupper($request['name']);
                            $carrier->code = strtoupper($request['code']);
                            $carrier->url = $request['url'] ? $request['url'] : null;

                            if ($carrier->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La paquetería ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($carrier);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la paquetería.');
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

                $carrier = Carriers::findFirst($id);

                if ($carrier) {
                    $carrier->setTransaction($tx);

                    if ($carrier->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La paquetería ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($carrier);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la paquetería.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La paquetería no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 22)
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
