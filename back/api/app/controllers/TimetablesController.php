<?php

use Phalcon\Mvc\Controller;

class TimetablesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getTimetables ()
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT t.id, t.job_title_id, t.name, s.name AS shift_name, t.check_in_time, t.check_out_time
                    FROM hrs_timetables AS t
                    INNER JOIN hrs_shifts AS s
                    ON t.job_title_id = s.id;";
            $data = $this->db->query($sql);
            $content['timetables'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getTimetable ($id)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $timetable = null;
            if (is_numeric($id)) {
                $sql = "SELECT t.id, t.name, t.job_title_id, s.name AS shift_name, t.check_in_time, t.check_out_time
                FROM hrs_timetables AS t
                INNER JOIN hrs_shifts AS s
                ON t.job_title_id = s.id
                WHERE t.id = $id;";
                $data = $this->db->query($sql);
                $timetable = $data->fetch();
            }
            $content['timetable'] = $timetable;
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getTimetablesByPagination ()
    {
        $request = $this->request->getPost();
        if ($this->userHasPermission()){
            $response = $this->getGridSQL($request);
            $this->content['timetables'] = $response['data'];
            $this->content['timetablesCount'] = $response['rowCounts'];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getGridSQL ($request) {
        $where = 'WHERE t.id > 0 ';
        $sortBy = " ORDER BY t.id ";
        $filter = $request['filter'];
        $pagination = $request['pagination'];        
        if (!empty($filter)){
            $where .= " AND ( p.id::text ILIKE '%".$filter."%' OR p.name ILIKE '%".$filter."%')";
        } 
        $desc = $pagination['descending'] === 'false' ? ' ASC ' : ' DESC '; // Al revés está bien
        $offset = " OFFSET " . (($pagination['page'] - 1) * $pagination['rowsPerPage']);
        $limit = " LIMIT " . $pagination['rowsPerPage'];
        $sql = "SELECT count(t.id) AS count
                FROM hrs_timetables AS t
                INNER JOIN hrs_shifts AS s
                ON t.job_title_id = s.id
                {$where}";
        $timetablesCount = $this->db->query($sql)->fetchAll();
            $sql = "SELECT t.id, t.job_title_id, t.name, s.name AS shift_name, t.check_in_time, t.check_out_time
                FROM hrs_timetables AS t
                INNER JOIN hrs_shifts AS s
                ON t.job_title_id = s.id
                {$where} {$sortBy} {$desc} {$offset} {$limit} ;";
        $data = $this->db->query($sql)->fetchAll();
        $response = array('data' => $data, 'rowCounts' => $timetablesCount[0]['count']);
        return $response;
    }

    public function getOptions () {
        $sql = "SELECT id AS value, name AS label, job_title_id AS shift
                FROM hrs_timetables
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
            $request = $this->request->getPost();
                if ($this->userHasPermission()) {
                    $actualTimetable = Timetables::findFirst("name = '".$request['name']."'AND job_title_id = '".$request['shift_id']."'");
                    if ($actualTimetable) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un horario con el mismo nombre dentro de este turno.');
                    } else {
                        if ($flagRegistry) {
                            $timetable = new Timetables();
                            $timetable->setTransaction($tx);
                            $timetable->job_title_id = $request['shift_id'];
                            $timetable->check_in_time = $request['time_entry'];
                            $timetable->check_out_time = $request['time_departure'];
                            $timetable->name = $request['name'];
                            $timetable->account_id = $actualAccount;
                            if ($timetable->create()) {
                                $this->content['timetable'] = $timetable;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El horario ha sido creado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($timetable);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el horario.');
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
                $request = $this->request->getPut();
                $actualTimetable = Timetables::findFirst("name = '".$request['name']."' AND job_title_id ='".$request['shift_id']."' AND id <> '".$id."'");
                if ($actualTimetable) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un horario con el mismo nombre dentro de este turno.');
                } else {
                $request = $this->request->getPut();
                    $tx = $this->transactions->get();
                    $timetable = Timetables::findFirst($id);
                    $flagRegistry = true;
                    if ($flagRegistry) {
                        if ($timetable) {
                            $timetable->setTransaction($tx);
                            $timetable->job_title_id = $request['shift_id'];
                            $timetable->check_in_time = $request['time_entry'];
                            $timetable->check_out_time = $request['time_departure'];
                            $timetable->name = $request['name'];
        
                            if ($timetable->update()) {
                                $this->content['timetable'] = $timetable;
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El horario ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($timetable);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el horario.');
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

                $timetable = Timetables::findFirst($id);

                if ($timetable) {
                    $timetable->setTransaction($tx);

                    if ($timetable->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El horario ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($timetable);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el horario.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El horario no existe.');
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
