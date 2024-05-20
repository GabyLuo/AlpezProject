<?php

use Phalcon\Mvc\Controller;

class CustomerBranchOfficesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getCustomerBranchOffices ($customerId)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($customerId)) {
                $sql = "SELECT cbo.id, cbo.customer_id, cbo.name, cbo.street, cbo.outdoor_number, cbo.zip_code, cbo.phone_number, 
                cbo.city,cbo.colony,cbo.municipality,cbo.int_number, cbo.state,
                TO_CHAR(cbo.open_horary, 'HH24:MI') AS open_horary, TO_CHAR(cbo.close_horary, 'HH24:MI') AS close_horary 
                FROM sls_customer_branch_offices AS cbo  WHERE customer_id = $customerId ORDER BY ID ASC;";
                $this->content['CustomerBranchOffices'] = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id de cliente válido.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getCustomerBranchOffice ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['CustomerBranchOffices'] = CustomerBranchOffices::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions ()
    {
        $sql = "SELECT id AS value, name AS label, customer_id AS customer FROM sls_customer_branch_offices ORDER BY name ASC;";
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

                $customerBranchOffice = new CustomerBranchOffices();
                $customerBranchOffice->setTransaction($tx);
                $customerBranchOffice->customer_id = strtoupper($request['customer_id']);
                $customerBranchOffice->name = strtoupper($request['name']);
                $customerBranchOffice->street = strtoupper($request['street']);
                $customerBranchOffice->outdoor_number = strtoupper($request['outdoor_number']);
                $customerBranchOffice->zip_code = strtoupper($request['zip_code']);
                $customerBranchOffice->phone_number = strtoupper($request['phone_number']);
                $customerBranchOffice->city = strtoupper($request['city']);
                $customerBranchOffice->municipality = strtoupper($request['municipality']);
                $customerBranchOffice->colony = strtoupper($request['colony']);
                $customerBranchOffice->int_number = strtoupper($request['int_number']);
                $customerBranchOffice->state = $request['state'] ? $request['state'] : null;
                $customerBranchOffice->phone_number = strtoupper($request['phone_number']);

                $customerBranchOffice->colony = $request['colony'];

                if ($customerBranchOffice->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Sucursal registrada correctamente.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($customerBranchOffice);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar la sucursal.');
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
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $customerBranchOffice = CustomerBranchOffices::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($customerBranchOffice) {
                    $customerBranchOffice->setTransaction($tx);
                    $customerBranchOffice->customer_id = strtoupper($request['customer_id']);
                    $customerBranchOffice->name = strtoupper($request['name']);
                    $customerBranchOffice->street = strtoupper($request['street']);
                    $customerBranchOffice->outdoor_number = strtoupper($request['outdoor_number']);
                    $customerBranchOffice->zip_code = strtoupper($request['zip_code']);
                    $customerBranchOffice->phone_number = strtoupper($request['phone_number']);
                    $customerBranchOffice->city = strtoupper($request['city']);
                    $customerBranchOffice->municipality = strtoupper($request['municipality']);
                    $customerBranchOffice->colony = strtoupper($request['colony']);
                    $customerBranchOffice->int_number = strtoupper($request['int_number']);
                    $customerBranchOffice->state = $request['state'] ? $request['state'] : null;

                    $customerBranchOffice->colony = $request['colony'];

                    if ($customerBranchOffice->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La sucursal ha sido modificada correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($customerBranchOffice);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la sucursal.');
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

    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $customerBranchOffice = CustomerBranchOffices::findFirst($id);

                if ($customerBranchOffice) {
                    $customerBranchOffice->setTransaction($tx);

                    if ($customerBranchOffice->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La sucursal ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($customerBranchOffice);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la sucursal.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El cliente no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 20 OR role_id = 17 OR role_id = 28 OR role_id = 22 OR role_id = 29 OR role_id = 4)
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
