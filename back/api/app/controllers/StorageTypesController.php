<?php

use Phalcon\Mvc\Controller;

class StorageTypesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getStorageTypes ()
    {   
        if ($this->userHasPermission()) {
            $this->content['storageTypes'] = StorageTypes::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }
    
    public function getStorageType ($id)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($id)) {
                $this->content['storageType'] = StorageTypes::findFirst($id);
                $this->content['result'] = true;
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id AS value, name AS label FROM wms_storage_types ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
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

                $storageType = StorageTypes::findFirst("account_id = $actualAccount AND name = '".strtoupper($request['name'])."'");
                if ($storageType) {
                    $this->content['message'] = Message::error('Ya existe un tipo de almacén con el mismo nombre.');
                } else {
                    $storageType = new StorageTypes();
                    $storageType->setTransaction($tx);
                    $storageType->name = strtoupper($request['name']);
                    $storageType->account_id = $actualAccount;

                    if ($storageType->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El tipo de almacén ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($storageType);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el tipo de almacén.');
                        $tx->rollback();
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

                $storage = StorageTypes::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $storageType = StorageTypes::findFirst("id <> $id AND account_id = $actualAccount AND name = '".strtoupper($request['name'])."'");
                if ($storageType) {
                    $this->content['message'] = Message::error('Ya existe un tipo de almacén con el mismo nombre.');
                } else {
                    $storageType = StorageTypes::findFirst($id);
                    if ($storageType && $storageType->account_id == $actualAccount) {
                        $storageType->setTransaction($tx);
                        $storageType->name = strtoupper($request['name']);

                        if ($storageType->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El tipo de almacén ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($storageType);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el tipo de almacén.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['message'] = Message::error('No se ha encontrado el tipo de almacén.');
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

                $storageType = StorageTypes::findFirst($id);

                if ($storageType) {
                    $storageType->setTransaction($tx);

                    if ($storageType->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El tipo de almacén ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($storageType);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el tipo de almacén.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El tipo de almacén no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 7 OR role_id = 22 OR role_id = 29)
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
