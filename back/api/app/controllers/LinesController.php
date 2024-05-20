<?php

use Phalcon\Mvc\Controller;

class LinesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getLines ($pt = 0)
    {
        //$content = $this->content;
        if ($this->userHasPermission()) {
            //$this->content['lines'] = Lines::find(['order' => 'id ASC']);
            //$this->content['result'] = true;
             $sql = "SELECT l.id, l.category_id, CONCAT(c.code,'-',l.code) AS code, l.name, c.name AS category_name
                     FROM wms_lines AS l
                     INNER JOIN wms_categories AS c
                     ON l.category_id = c.id";
            
              $data = $this->db->query($sql)->fetchAll();
             $this->content['lines'] = $data;
             $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getLine ($id)
    {
        // $content = $this->content;
        if ($this->userHasPermission()) {
            $this->content['line'] = Lines::findFirst([$id]);
            $this->content['result'] = true;
            // $line = null;
            // if (is_numeric($id)) {
            //     $sql = "SELECT l.*, c.name AS category
            //             FROM wms_lines AS l
            //             INNER JOIN wms_categories AS c
            //             ON l.category_id = c.id
            //             WHERE l.id = $id;";
                
            //     $data = $this->db->query($sql);
            //     $data->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            //     $line = $data->fetch();
            // }
            // $content['line'] = $line;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getOptions () {
        $sql = "SELECT id AS value, name AS label, category_id AS category
                FROM wms_lines
                ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getOptionsByCategoryId ($categoryId) {
        $options = [];
        if (is_numeric($categoryId)) {
            $sql = "SELECT l.id AS value, CONCAT(l.name, ' (', c.name, ')') AS label
                    FROM wms_lines AS l
                    INNER JOIN wms_categories AS c
                    ON l.category_id = c.id
                    WHERE category_id = $categoryId
                    ORDER BY c.name ASC;";
            $options = $this->db->query($sql)->fetchAll();
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

                $actualLine = Lines::findFirst("category_id = '".$request['category_id']."' AND code = '".$request['code']."'");

                if ($actualLine) {
                    $this->content['message'] = Message::error('Ya se encuentra registrada una línea con el mismo código y categoría.');
                } else {
                    $actualLine = Lines::findFirst("category_id = '".$request['category_id']."' AND name = '".$request['name']."'");
                    if ($actualLine) {
                        $this->content['message'] = Message::error('Ya se encuentra registrada una línea con el mismo nombre y categoría.');
                    } else {
                        $line = new Lines();
                        $line->setTransaction($tx);
                        $line->name = strtoupper($request['name']);
                        $line->code = strtoupper($request['code']);
                        $line->category_id = $request['category_id'];

                        if ($line->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La línea ha sido creada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($line);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la línea.');
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

                $request = $this->request->getPut();

                $actualLine = Lines::findFirst("category_id = '".$request['category_id']."' AND code = '".$request['code']."' AND id <> '".$id."'");

                if ($actualLine) {
                    $this->content['message'] = Message::error('Ya se encuentra registrada una línea con el mismo código y categoría.');
                } else {
                    $actualLine = Lines::findFirst("category_id = '".$request['category_id']."' AND name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualLine) {
                        $this->content['message'] = Message::error('Ya se encuentra registrada una línea con el mismo nombre y categoría.');
                    } else {
                        $line = Lines::findFirst($id);

                        if ($line) {
                            $line->setTransaction($tx);
                            $line->name = strtoupper($request['name']);
                            $line->code = strtoupper($request['code']);
                            $line->category_id = $request['category_id'];

                            if ($line->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La línea ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($line);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la línea.');
                                // $tx->rollback();
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

                $line = Lines::findFirst($id);

                if ($line) {
                    $line->setTransaction($tx);

                    if ($line->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La línea ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($line);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la línea.');
                        }
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La línea no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 22 )
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
