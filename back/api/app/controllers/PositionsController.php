<?php

use Phalcon\Mvc\Controller;

class PositionsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getPositions ()
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT p.id, p.area_id, a.name AS areas, a.department_id, d.name AS departments, p.name
                    FROM hrs_positions AS p
                    INNER JOIN hrs_areas AS a
                    ON p.area_id = a.id
                    INNER JOIN hrs_departments AS d
                    ON a.department_id = d.id;";
            $data = $this->db->query($sql);
            $content['positions'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getPositionsByPagination ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getGridSQL($request);
            $this->content['positions'] = $response['data'];
            $this->content['positionsCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getGridSQL ($request) {
        $where = 'WHERE p.id > 0 ';
        $sortBy = " ORDER BY p.id ";
        $filter = $request['filter'];
        $pagination = $request['pagination'];        
        if (!empty($filter)){
            $where .= " AND ( p.id::text ILIKE '%".$filter."%' OR p.name ILIKE '%".$filter."%')";
        } 
        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];
        $sql = "SELECT count(p.id) AS count
                FROM hrs_positions AS p
                INNER JOIN hrs_areas AS a 
                ON p.area_id = a.id
                INNER JOIN hrs_departments AS d 
                ON a.department_id = d.id
            {$where}";
        $positionsCount = $this->db->query($sql)->fetchAll();
        $sql = "SELECT p.id, p.area_id, a.name AS areas, a.department_id, d.name AS departments, p.name
                FROM hrs_positions AS p
                INNER JOIN hrs_areas AS a
                ON p.area_id = a.id
                INNER JOIN hrs_departments AS d 
                ON a.department_id = d.id
                {$where} {$sortBy} {$desc} {$offset} {$limit} ;";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data, 'rowCounts' => $positionsCount[0]['count']);
        return $response;
    }

    public function getPosition ($id)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $position = null;
            if (is_numeric($id)) {
                $sql = "SELECT p.id, p.name, p.area_id, a.name AS area, a.department_id, d.name AS department
                        FROM hrs_positions AS p
                        INNER JOIN hrs_areas AS a
                        ON p.area_id = a.id
                        INNER JOIN hrs_departments AS d
                        ON a.department_id = d.id
                        WHERE p.id = $id;";
                $data = $this->db->query($sql);
                $position = $data->fetch();
            }
            $content['position'] = $position;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getOptions () {
        $sql = "SELECT id AS value, name AS label, area_id AS area
            FROM hrs_positions
            ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);   
    }

    public function create ()
    {
        try {
            $flagRegistry = true;
            $tx = $this->transactions->get();
            $request = $this->request->getPost();
            $validUser = Auth::getUserData($this->config);
            $actualAccount = Auth::getUserAccount($validUser->id);
            $content = $this->content;

            if (is_numeric($request['area_id'])) {
                if ($this->userHasPermission()) {
                    $sql = "SELECT id
                            FROM hrs_positions
                            WHERE area_id = ".$request['area_id']."
                            AND name = '".$request['name']."';";
                    $data = $this->db->query($sql);
                    $positions = $data->fetchAll();
                    if (count($positions) > 0) {
                        $flagRegistry = false;
                        $this->content['result'] = false;
                        $this->content['message'] = Message::success('Ya se encuentra registrado un puesto con el mismo nombre y línea.');
                    }

                    if ($flagRegistry) {
                        $position = new Positions();
                        $position->setTransaction($tx);
                        $position->name = strtoupper($request['name']);
                        $position->area_id = $request['area_id'];
                        $position->account_id = $actualAccount;
                        if ($position->create()) {
                            $this->content['position'] = $position;
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El puesto ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($position);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el puesto.');
                            // $tx->rollback();
                        }
                    }
                } else {
                    $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
                }
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
                $request = $this->request->getPut();
                if (is_numeric($id) && isset($request['area_id']) && is_numeric($request['area_id'])) {
                    $tx = $this->transactions->get();
                    $position = Positions::findFirst($id);
                    $flagRegistry = true;
        
                    $sql = "SELECT id
                            FROM hrs_positions
                            WHERE id <> $id
                            AND area_id = ".$request['area_id']."
                            AND name = '".$request['name']."';";
                    $data = $this->db->query($sql);
                    $positions = $data->fetchAll();
                    if (count($positions) > 0) {
                        $flagRegistry = false;
                        $this->content['result'] = false;
                        $this->content['message'] = Message::success('Ya se encuentra registrado un puesto con el mismo nombre y arae.');
                    }
                    if ($flagRegistry) {
                        if ($position) {
                            $position->setTransaction($tx);
                            $position->name = strtoupper($request['name']);
                            $position->area_id = $request['area_id'];
        
                            if ($position->update()) {
                                $this->content['position'] = $position;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El puesto ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($position);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el pusto.');
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

                $position = Positions::findFirst($id);

                if ($position) {
                    $position->setTransaction($tx);

                    if ($position->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El puesto ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($position);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el puesto.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El puesto no existe.');
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
