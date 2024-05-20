<?php

use Phalcon\Mvc\Controller;

class CategoriesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getCategories ()
    {
        if ($this->userHasPermission()) {
            $this->content['categories'] = Categories::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getCategory ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['category'] = Categories::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM wms_categories ORDER BY name ASC;";
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

                $actualCategory = Categories::findFirst("code = '".$request['code']."'");

                if ($actualCategory) {
                    $this->content['message'] = Message::success('Ya se encuentra registrada una categoría con el mismo código.');
                } else {
                    $actualCategory = Categories::findFirst("name = '".$request['name']."'");
                    if ($actualCategory) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una categoría con el mismo nombre.');
                    } else {
                        $category = new Categories();
                        $category->setTransaction($tx);
                        $category->name = strtoupper($request['name']);
                        $category->code = strtoupper($request['code']);
                        $category->account_id = $actualAccount;

                        if ($category->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La categoría ha sido creada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($category);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la categoría.');
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

                $category = Categories::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualCategory = Categories::findFirst("code = '".$request['code']."' AND id <> '".$id."'");

                if ($actualCategory) {
                    $this->content['message'] = Message::success('Ya se encuentra registrada una categoría con el mismo código.');
                } else {
                    $actualCategory = Categories::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualCategory) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una categoría con el mismo nombre.');
                    } else {
                        if ($category) {
                            $category->setTransaction($tx);
                            $category->name = strtoupper($request['name']);
                            $category->code = strtoupper($request['code']);
                            $category->account_id = $actualAccount;

                            if ($category->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La categoría ha sido modificada.');
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

                $category = Categories::findFirst($id);

                if ($category) {
                    $category->setTransaction($tx);

                    if ($category->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La categoría ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($category);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la categoría.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 22 OR role_id = 22)
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
