<?php

use Phalcon\Mvc\Controller;

class VacationsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getVacations()
    {
        if ($this->userHasPermission()) {
            $this->content['vacations'] = Vacations::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getVacation ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['vacation'] = Vacations::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, year, day FROM hrs_vacation_grants ORDER BY year ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['id'],
                'label' => $type['date']
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

                $actualVacation = Vacations::findFirst("year = '".$request['years']."'");
                    if ($actualVacation) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado el consepto de vacaciones con los mismos detalles.');
                    } else {
                        $vacation = new Vacations();
                        $vacation->setTransaction($tx);
                        $vacation->year = $request['years'];
                        $vacation->day = $request['days'];
                        $vacation->account_id = $actualAccount;

                        if ($vacation->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El derecho a vaciones ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($vacation);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el derecho a vacaiones.');
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

                $vacation = Vacations::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualVacation = Vacations::findFirst("year = '".$request['year']."' AND id <> '".$id."'");
                    if ($actualVacation) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado el consepto de vacaciones con los mismos detalles.');
                    } else {
                        if ($vacation) {
                            $vacation->setTransaction($tx);
                            $vacation->year = $request['year'];
                            $vacation->day = $request['day'];
                            $vacation->account_id = $actualAccount;

                            if ($vacation->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El derecho a vaciones ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($vacation);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el derecho a vacaiones.');
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

                $vacations = Vacations::findFirst($id);

                if ($vacations) {
                    $vacations->setTransaction($tx);

                    if ($vacations->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El derecho a vacaciones ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($vacations);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el derecho a vacaciones.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El derecho a vacaciones no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 4 OR role_id = 5 OR role_id = 7)
                    AND id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }

    public function createRequest () {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $dateFlag = true;
                $requestFlag = true;

                $newDate = new DateTime($request['until']);
                $newDate->add(new DateInterval('P1D')); 
                $fomattedDate = $newDate->format('Y-m-d');

                $period = new DatePeriod(
                    new DateTime($request['since']),
                    new DateInterval('P1D'),
                    new DateTime($fomattedDate)
                );
                
                //Validacion de solicitud de vacaiones - incidencias
                $sql = "SELECT assistance_date FROM hrs_incidencias WHERE employee_id = ".$request['employee_id'];
                $incidencia = $this->db->query($sql)->fetchAll();
                foreach ($period as $key => $value) {
                    foreach ($incidencia as $cop => $val) {
                        if ($value->format('d/m/Y') == $val['assistance_date']) {
                            $dateFlag = false;
                        }
                    }
                }

                //Validacion de solicitud de vacaiones - solicitudes
                $sql = "SELECT date_requested FROM hrs_vacations_request WHERE employee_id = ".$request['employee_id'];
                $incidencia = $this->db->query($sql)->fetchAll();
                foreach ($period as $key => $value) {
                    foreach ($incidencia as $cop => $val) {
                        if ($value->format('Y-m-d') == $val['date_requested']) {
                            $requestFlag = false;
                        }
                    }
                }

                if ($dateFlag) {
                    if ($requestFlag) {
                        foreach ($period as $key => $value) {
                            //$value->format('Y-m-d')
                            $vacation = new VacationsRequest();
                            $vacation->setTransaction($tx);
                            $vacation->employee_id = $request['employee_id'];
                            $vacation->date_requested = $value->format('Y-m-d');
                            $vacation->status = false;
                            $vacation->account_id = $actualAccount;
                            $vacation->create();
                        }
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La Soliciitud ha sido creada.');
                        $tx->commit();
                    } else {
                        $this->content['result'] = false;
                        $this->content['message'] = Message::success('Ya se encuentra solicitada alguna de las fechas solicitadas.');
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('Ya se encuentra registrada una fecha seleccionada en incidencias.');
                }
                    
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    function getVacationRequest () {
        if ($this->userHasPermission()) {
            $sql = "SELECT vr.id, vr.date_requested, vr.status, CONCAT(e.name,' ',e.paternal,' ',e.mathers) as name
            FROM hrs_vacations_request AS vr 
            INNER JOIN hrs_employees AS e ON vr.employee_id = e.id
            WHERE vr.status = false
            ORDER BY vr.id ASC";

            $this->content['vacationsRequest'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    function getVacationRequestTrue () {
        if ($this->userHasPermission()) {
            $sql = "SELECT vr.id, vr.date_requested, vr.status, CONCAT(e.name,' ',e.paternal,' ',e.mathers) as name
            FROM hrs_vacations_request AS vr 
            INNER JOIN hrs_employees AS e ON vr.employee_id = e.id
            WHERE vr.status = true
            ORDER BY vr.id ASC";

            $this->content['vacationsRequest'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    function getVacationRequestFilter ($id) {
        if ($this->userHasPermission()) {
            $sql = "SELECT vr.id, vr.date_requested, vr.status, CONCAT(e.name,' ',e.paternal,' ',e.mathers) as name
            FROM hrs_vacations_request AS vr 
            INNER JOIN hrs_employees AS e ON vr.employee_id = e.id
            WHERE vr.status = false AND vr.employee_id = $id
            ORDER BY vr.id ASC";

            $this->content['vacationsRequestFilter'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    function getVacationRequestFilterTrue ($id) {
        if ($this->userHasPermission()) {
            $sql = "SELECT vr.id, vr.date_requested, vr.status, CONCAT(e.name,' ',e.paternal,' ',e.mathers) as name
            FROM hrs_vacations_request AS vr 
            INNER JOIN hrs_employees AS e ON vr.employee_id = e.id
            WHERE vr.status = true AND vr.employee_id = $id
            ORDER BY vr.id ASC";

            $this->content['vacationsRequestFilter'] = $this->db->query($sql)->fetchAll();
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function approveRequest ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $dateFlag = true;

                $vacation = VacationsRequest::findFirst($id);
                $date = new DateTime($vacation->date_requested);

                $sql = "SELECT assistance_date FROM hrs_incidencias WHERE employee_id = ".$vacation->employee_id;
                $incidencia = $this->db->query($sql)->fetchAll();
                foreach ($incidencia as $cop => $val) {
                    if ($val['assistance_date'] ==  $date->format('d/m/Y')) {
                        $dateFlag = false;
                    }
                }

                if ($dateFlag) {
                    if ($vacation) {
                        $vacation->setTransaction($tx);
                        $vacation->status = true;
                        $vacation->account_id = $actualAccount;

                        $incidencia = new CaptureIncidencias();
                        $incidencia->setTransaction($tx);
                        $incidencia->employee_id = $vacation->employee_id;
                        $incidencia->assistance_type = 4;
                        $incidencia->assistance_date = $date->format('d/m/Y');
                        $incidencia->account_id = $actualAccount;
                        $incidencia->create();
    
                        if ($vacation->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La Solicitud ha sido aprobada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($vacation);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar aprobar la Solicitud.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['result'] = false;
                        $this->content['message'] = Message::success('La Solicitud no existe.');
                    }
                } else {
                    $this->content['result'] = false;
                    $this->content['message'] = Message::success('La fecha seleccionada no se encuentra disponible.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    function deleteRequest ($id){
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $vacations = VacationsRequest::findFirst($id);

                if ($vacations) {
                    $vacations->setTransaction($tx);

                    if ($vacations->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La Solicitud ha sido denegada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($vacations);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar denegar la Solicitud.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La Solicitud no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
}
