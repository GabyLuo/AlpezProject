<?php

use Phalcon\Mvc\Controller;

class ProductionProcessesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getProductionProcesses ()
    {
        if ($this->userHasPermission()) {
            $this->content['productionProcesses'] = ProductionProcesses::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getProductionProcess ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['productionProcess'] = ProductionProcesses::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id AS value, name AS label FROM prd_processes ORDER BY id ASC;";
        $options = $this->db->query($sql)->fetchAll();
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

                $productionProcess = new ProductionProcesses();
                $productionProcess->setTransaction($tx);
                $productionProcess->name = strtoupper($request['name']);

                if ($productionProcess->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El tipo de proceso de producción ha sido creado correctamente.');
                    $this->content['productionProcess'] = $productionProcess;
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($productionProcess);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el tipo de proceso de producción.');
                    $tx->rollback();
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
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();

                    $productionProcess = ProductionProcesses::findFirst($id);

                    $request = $this->request->getPut();

                    if ($productionProcess) {
                        $productionProcess->setTransaction($tx);
                        $productionProcess->name = strtoupper($request['name']);

                        if ($productionProcess->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El tipo de proceso de producción ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($productionProcess);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el tipo de proceso de producción.');
                            $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        }
        
        $this->response->setJsonContent($this->content);
    }

    public function delete ($id)
    {
        if (is_numeric($id)) {
            try {
                if ($this->userHasPermission()) {
                    $tx = $this->transactions->get();
            
                    $productionProcess = ProductionProcesses::findFirst($id);
            
                    if ($productionProcess) {
                        $productionProcess->setTransaction($tx);
                
                        if ($productionProcess->delete()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El tipo de proceso de producción ha sido eliminado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($productionProcess);
                            if ($this->content['error'][1]) {
                                $this->content['message'] = Message::error($this->content['error'][1]);
                            } else {
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el tipo de proceso de producción.');
                            }
                            // $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('El productionProcesse no existe.');
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
            } catch (Exception $e) {
                $this->content['errors'] = Message::exception($e);
            }
        } else {
            $this->content['message'] = Message::success('No se ha recibido una id válida.');
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 5 OR role_id = 6 OR role_id = 7)
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
