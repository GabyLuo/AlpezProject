<?php

use Phalcon\Mvc\Controller;

class OutputsTypesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getOutputs ()
    {
        if ($this->userHasPermission()) {
            $this->content['outputs'] = OutputsTypes::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getOutput ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['output'] = OutputsTypes::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM fin_outputs_types ORDER BY name ASC;";
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
                $actualOutput = OutputsTypes::findFirst("code = '".$request['code']."'");

                if ($actualOutput) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un rubro con el mismo código.');
                } else {
                    $actualOutput = OutputsTypes::findFirst("name = '".$request['name']."'");
                    if ($actualOutput) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un rubro con el mismo nombre.');
                    } else {
                        $output = new OutputsTypes();
                        $output->setTransaction($tx);
                        $output->name = strtoupper($request['name']);
                        $output->code = strtoupper($request['code']);
                        $output->account_id = $actualAccount;

                        if ($output->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El rubro ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($output);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el rubro.');
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
                $output = OutputsTypes::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualOutput = OutputsTypes::findFirst("code = '".$request['code']."' AND id <> '".$id."'");

                if ($actualOutput) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un rubro con el mismo código.');
                } else {
                    $actualOutput = OutputsTypes::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualOutput) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un rubro con el mismo nombre.');
                    } else {
                        if ($output) {
                            $output->setTransaction($tx);
                            $output->name = strtoupper($request['name']);
                            $output->code = strtoupper($request['code']);
                            $output->account_id = $actualAccount;

                            if ($output->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El rubro ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($output);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el rubro.');
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

                $output = OutputsTypes::findFirst($id);

                if ($output) {
                    $output->setTransaction($tx);

                    if ($output->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El rubro ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($output);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el rubro.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El rubro no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 24)
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
