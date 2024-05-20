<?php

use Phalcon\Mvc\Controller;

class CustomerContactsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getCustomerContacts ($customerId)
    {
        if ($this->userHasPermission()) {
            if (is_numeric($customerId)) {
                $sql = "SELECT scc.id, scc.customer_id, scc.name, scc.cellphone as phone, scc.telephone as tel, scc.email, scc.send_invoice,scc.apartment
                FROM sls_customer_contacts AS scc
                WHERE customer_id = $customerId
                ORDER BY ID ASC;";
                $this->content['contacts'] = $this->db->query($sql)->fetchAll();
                $this->content['result'] = true;
            } else {
                $this->content['message'] = Message::error('No se ha recibido un id de cliente válido.');
            }
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getCustomerContact ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['contacts'] = CustomerContacts::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions ()
    {
        $sql = "SELECT id AS value, name AS label, customer_id AS customer FROM sls_customer_contacts ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getContacts ($id) {
        if ($this->userHasPermission()) {
            $sql="SELECT id, name from sls_customer_contacts where customer_id = $id";
            $query = $this->db->query($sql)->fetchAll();
            $options = [];
            foreach($query as $value){
                $options[] = [
                  'label' => $value['name'],
                  'value' => $value['id']
                ];
            }
            $this->content['contacts'] = $options;
            $this->content['result'] = true;
        }else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $request = $this->request->getPost();

                $customerContacts = new CustomerContacts();
                $customerContacts->setTransaction($tx);
                $customerContacts->customer_id = intval($request['customer_id']);
                $customerContacts->name = $request['name'] ? strtoupper($request['name']) : null;
                $customerContacts->cellphone = $request['phone'] ? $request['phone'] : null;
                $customerContacts->telephone = $request['tel'] ? $request['tel'] : null;
                $customerContacts->email = $request['email'] ? $request['email'] : null ;
                $customerContacts->send_invoice = strval($request['send_invoice']);
                $customerContacts->apartment = $request['apartment'] ? strtoupper($request['apartment']) : null;
                if ($customerContacts->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Contacto registrada correctamente.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($customerContacts);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el contacto.');
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

                $customerContacts = CustomerContacts::findFirst($id);

                $request = $this->request->getPut();
                //print_r($request);
                //exit();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($customerContacts) {
                    $customerContacts->setTransaction($tx);
                    $customerContacts->customer_id = intval($request['customer_id']);
                    $customerContacts->name = $request['name'] ? strtoupper($request['name']) : null;
                    $customerContacts->cellphone = $request['phone'] ? $request['phone'] : null;
                    $customerContacts->telephone = $request['tel'] ? $request['tel'] : null;
                    $customerContacts->email = $request['email'] ? $request['email'] : null ;
                    /*if(($request['send_invoice'])){
                                $customerContacts->send_invoice = ($request['send_invoice'] == '') ? null : intval($request['send_invoice']);
                            }else {
                                $customerContacts->send_invoice = ($request['send_invoice']['value'] == '') ? null : ($request['send_invoice']['value']);
                            }*/
                        
                    $customerContacts->send_invoice = strval($request['send_invoice']);
                    $customerContacts->apartment = $request['apartment'] ? strtoupper($request['apartment']) : null;
                    if ($customerContacts->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El contacto ha sido modificado correctamente.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($customerContacts);
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

                $customerContacts = CustomerContacts::findFirst($id);

                if ($customerContacts) {
                    $customerContacts->setTransaction($tx);

                    if ($customerContacts->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El contacto ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($customerContacts);
                        if ($this->content['error'][0]) {
                            $this->content['message'] = Message::error($this->content['error'][0]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el contacto.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3 OR role_id = 20 OR role_id = 4  OR role_id = 17 OR role_id = 22 OR role_id = 29 OR role_id = 27 OR role_id = 28 OR role_id = 29)
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
