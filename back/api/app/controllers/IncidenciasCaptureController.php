<?php

use Phalcon\Mvc\Controller;

class IncidenciasCaptureController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getIncidencias($id)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT i.assistance_type AS tipo_id, i.assistance_date AS fecha, t.name AS nombre
                FROM hrs_incidencias AS i
                INNER JOIN hrs_employees AS e
                ON e.id = i.employee_id
                INNER JOIN hrs_incidencia_type AS t
                ON i.assistance_type = t.id
                WHERE i.employee_id = $id
                ORDER BY i.assistance_date DESC";
            $data = $this->db->query($sql);
            $content['capturaIncidencias'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getIncidenciasVacations($id)
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT COUNT(i.id) AS  dias FROM hrs_incidencias AS i
            INNER JOIN hrs_employees AS e ON e.id = i.employee_id
            INNER JOIN hrs_incidencia_type AS t ON i.assistance_type = t.id
            WHERE i.employee_id = $id AND t.id = 4";
            $data = $this->db->query($sql);
            $content['dias'] = $data->fetchAll();

            $sqlf = "SELECT i.assistance_date as date FROM hrs_incidencias AS i
            INNER JOIN hrs_employees AS e ON e.id = i.employee_id
            INNER JOIN hrs_incidencia_type AS t ON i.assistance_type = t.id
            WHERE i.employee_id = $id AND t.id = 4
            ORDER BY i.assistance_date DESC";
            $dataf = $this->db->query($sqlf);
            $content['fechas'] = $dataf->fetchAll();

        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $flag = false;
                $sql = "SELECT assistance_date FROM hrs_incidencias WHERE employee_id = ".$request['employee_id'];
                $dataf = $this->db->query($sql);
                $flagDate = $content['assistance_date'] = $dataf->fetchAll();

                for ($i=0; $i < count($flagDate); $i++) { 
                    if ($flagDate[$i]['assistance_date'] == $request['assistance_date']) {
                        $flag = true;
                    }
                }

                if ($flag) {
                    $this->content['message'] = Message::success('Ya se encuentra registreda una incidencia en esta fecha.');
                } else {
                    $incidencia = new CaptureIncidencias();
                    $incidencia->setTransaction($tx);
                    $incidencia->employee_id = $request['employee_id'];
                    $incidencia->assistance_type = $request['assistance_type']['value'];
                    $incidencia->assistance_date = $request['assistance_date'];
                    $incidencia->account_id = $actualAccount;
                    if ($incidencia->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La incidencia ha sido creada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($incidencia);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la incidencia.');
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

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 4)
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
