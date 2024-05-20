<?php

use Phalcon\Mvc\Controller;

class EmployeesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getEmployees()
    {
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT e.id, e.code, e.name, e.paternal, e.mathers, a.name AS area, d.name AS departments, p.name AS puesto
                FROM hrs_employees AS e
                INNER JOIN hrs_positions AS p
                ON e.position_id = p.id
                INNER JOIN hrs_areas AS a
                ON p.area_id = a.id
                INNER JOIN hrs_departments AS d
                ON a.department_id = d.id;";
            $data = $this->db->query($sql);
            $content['employees'] = $data->fetchAll();
        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }
    
    public function getEmployee ($id)
    {
        $sql = "SELECT id, timetable_id FROM hrs_employees where id =".$id;
        $dataT = $this->db->query($sql);
        $flag = $contentIn['timetable'] = $dataT->fetch();
        $innerTime = '';
        $queryTime = '';
        if(!empty($flag['timetable_id'])){
            $innerTime = 'INNER JOIN hrs_timetables AS t ON t.id = e.timetable_id';
            $queryTime = ', t.id AS timetable_id, t.name AS timetable';
        }
        $content = $this->content;
        if ($this->userHasPermission()) {
            $sql = "SELECT e.*, a.name AS area, a.id AS area_id, d.name AS department, d.id AS department_id, p.name AS position, p.id AS position_id, s.name AS shift, s.id AS shift_id{$queryTime}
            FROM hrs_employees AS e
            {$innerTime}
            INNER JOIN hrs_shifts AS s
            ON s.id = e.shift_id
            INNER JOIN hrs_positions AS p
            ON e.position_id = p.id
            INNER JOIN hrs_areas AS a
            ON p.area_id = a.id
            INNER JOIN hrs_departments AS d
            ON a.department_id = d.id
            WHERE e.id = $id";
            $data = $this->db->query($sql);
            $content['employee'] = $data->fetch();

        } else {
            $content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getOptions () {
        $sql = "SELECT id, name, paternal, mathers FROM hrs_employees ORDER BY name ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['name'].' '.$type['paternal'].' '.$type['mathers']
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

                $actualEmployee = Employees::findFirst("code = '".$request['code']."'");
                    if ($actualEmployee) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un empleado con el mismo codigo.');
                    } else {
                        $employee = new Employees();
                        $employee->setTransaction($tx);
                        $employee->code = strtoupper($request['code']);
                        $employee->position_id = intval($request['position']['value']);
                        $employee->shift_id = intval($request['shift']['value']);
                        $employee->location = strtoupper($request['location']);
                        $employee->payment_method = strtoupper($request['payment_method']);
                        $employee->date_entry = date($request['date_entry']);
                        if(!empty($request['out_date'])) {
                            $employee->out_date = date($request['out_date']);
                        }
                        $employee->lada = $request['lada'];
                        $employee->status = strtoupper($request['status']['value']);
                        if(!empty($request['timetable']['value'])){
                            $employee->timetable_id = intval($request['timetable']['value']);
                        }
                        $employee->name = strtoupper($request['name']);
                        $employee->paternal = strtoupper($request['paternal']);
                        $employee->mathers = strtoupper($request['mathers']);
                        if(!empty($request['birth_date'])) {
                            $employee->birth_date = date($request['birth_date']);
                        }
                        $employee->curp = strtoupper($request['curp']);
                        $employee->rfc = strtoupper($request['rfc']);
                        if(!empty($request['social_security'])){
                            $employee->social_security = $request['social_security'];
                        }
                        $employee->phone = $request['phone'];
                        $employee->blood_type = strtoupper($request['blood_type']);
                        $employee->studies = strtoupper($request['studies']);
                        $employee->specialty = strtoupper($request['specialty']);
                        $employee->expertise = strtoupper($request['expertise']);
                        $employee->street = strtoupper($request['street']);
                        $employee->colony = strtoupper($request['colony']);
                        $employee->municipality = strtoupper($request['municipality']);
                        if(!empty($request['zip_code'])){
                            $employee->zip_code = $request['zip_code'];
                        }
                        $employee->birth_state = strtoupper($request['birth_state']);
                        $employee->birth_city = strtoupper($request['birth_city']);
                        $employee->account_id = $actualAccount;
                        $employee->salary = $request['salary'];
                        $employee->salary_imss = $request['salary_imss'];
                        
                        if ($employee->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El empleado ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($employee);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el empleado.');
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
                $employee = Employees::findFirst($id);
                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualEmployee = Employees::findFirst("code = '".$request['code']."' AND id <> '".$id."'");
                    if ($actualEmployee) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un empleado con el mismo codigo.');
                    } else {
                        if ($employee) {
                            $employee->setTransaction($tx);
                            $employee->code = strtoupper($request['code']);
                            $employee->position_id = intval($request['position']['value']);
                            $employee->shift_id = intval($request['shift']['value']);
                            $employee->location = strtoupper($request['location']);
                            $employee->payment_method = strtoupper($request['payment_method']);
                            $employee->date_entry = $request['date_entry'];
                            $employee->timetable_id = intval($request['timetable']['value']);
                            if(!empty($request['out_date'])) {
                                $employee->out_date = date($request['out_date']);
                            }
                            $employee->lada = $request['lada'];
                            if($request['status'] == 'ACTIVO' || $request['status'] == 'INACTIVO' ){
                                $employee->status = strtoupper($request['status']);
                            }else{
                                $employee->status = strtoupper($request['status']['value']);
                            }
                            $employee->name = strtoupper($request['name']);
                            $employee->paternal = strtoupper($request['paternal']);
                            $employee->mathers = strtoupper($request['mathers']);
                            if(!empty($request['birth_date'])) {
                                $employee->birth_date = date($request['birth_date']);
                            }
                            $employee->curp = strtoupper($request['curp']);
                            $employee->rfc = strtoupper($request['rfc']);
                            if(!empty($request['social_security'])){
                                $employee->social_security = $request['social_security'];
                            }
                            $employee->phone = strtoupper($request['phone']);
                            $employee->blood_type = strtoupper($request['blood_type']);
                            $employee->studies = strtoupper($request['studies']);
                            $employee->specialty = strtoupper($request['specialty']);
                            $employee->expertise = strtoupper($request['expertise']);
                            $employee->street = strtoupper($request['street']);
                            $employee->colony = strtoupper($request['colony']);
                            $employee->municipality = strtoupper($request['municipality']);
                            if(!empty($request['zip_code'])){
                                $employee->zip_code = $request['zip_code'];
                            }
                            $employee->birth_state = strtoupper($request['birth_state']);
                            $employee->birth_city = strtoupper($request['birth_city']);
                            $employee->account_id = $actualAccount;
                            if ($employee->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El empleado ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($employee);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el empleado.');
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

                $employees = Employees::findFirst($id);

                if ($employees) {
                    $employees->setTransaction($tx);

                    if ($employees->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El empleado ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($employees);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el empleado.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El empleado no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 6 OR role_id = 7)
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
