<?php

use Phalcon\Mvc\Controller;

class RepositoriesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getAll ()
    {
        if ($this->userHasPermission()) {
            $this->content['info'] = Repositories::find(['order' => 'id ASC', "parent_id is null"]);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function getByParent ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['info'] = Repositories::find(['order' => 'id ASC', "parent_id = {$id}"]);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function get ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['info'] = Repositories::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM sys_repositories ORDER BY name ASC;";
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

                $actual = Repositories::findFirst("name = '".$request['name']."'");
                if ($actual) {
                    $this->content['message'] = Message::success('Ya se encuentra registrada una dirección con el mismo nombre.');
                } else {
                    $repository = new Repositories();
                    $repository->setTransaction($tx);
                    $repository->name = $request['name'];
                    $repository->route = !empty($request['route']) ? $request['route'] : NULL;
                    $repository->sequence = !empty($request['sequence']) ? $request['sequence'] : NULL;
                    $repository->icon = !empty($request['icon']) ? $request['icon'] : NULL;
                    $repository->account_id = $actualAccount;
                    $repository->parent_id = !empty($request['parent_id']) ? $request['parent_id'] : NULL;
                    
                    if ($repository->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La dirección ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($repository);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la dirección.');
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

                $repository = Repositories::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actual = Repositories::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                if ($actual) {
                    $this->content['message'] = Message::success('Ya se encuentra registrada una dirección con el mismo nombre.');
                } else {
                    if ($repository) {
                        $repository->setTransaction($tx);
                        $repository->name = !empty($request['name']) ? $request['name'] : NULL;
                        $repository->route = !empty($request['route']) ? $request['route'] : NULL;
                        $repository->sequence = !empty($request['sequence']) ? $request['sequence'] : NULL;
                        $repository->icon = !empty($request['icon']) ? $request['icon'] : NULL;
                        $repository->account_id = $actualAccount;

                        if ($repository->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La dirección ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($repository);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la dirección.');
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

                $repository = Repositories::findFirst($id);

                if ($repository) {
                    $repository->setTransaction($tx);

                    if ($repository->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La dirección ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($repository);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la dirección .');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La dirección no existe.');
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
                    FROM sys_user_roles
                    WHERE ( role_id = 1)
                    AND user_id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
}
