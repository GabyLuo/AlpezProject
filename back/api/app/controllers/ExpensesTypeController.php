<?php

use Phalcon\Mvc\Controller;

class ExpensesTypeController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getExpenses ()
    {
        if ($this->userHasPermission()) {
            $this->content['expenses'] = ExpensesType::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getExpense ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['expense'] = ExpensesType::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM log_expenses_type ORDER BY name ASC;";
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

                $actualExpense = ExpensesType::findFirst("name = '".$request['name']."'");
                if ($actualExpense) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Tipo de gasto con el mismo nombre.');
                } else {
                    $expense = new ExpensesType();
                    $expense->setTransaction($tx);
                    $expense->name = strtoupper($request['name']);
                    $expense->account_id = $actualAccount;
                    if ($expense->create()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Tipo de gasto ha sido creado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($expense);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el Tipo de gasto.');
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
                $expense = ExpensesType::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualExpense = ExpensesType::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                if ($actualExpense) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un Tipo de gasto con el mismo nombre.');
                } else {
                    if ($expense) {
                        $expense->setTransaction($tx);
                        $expense->name = strtoupper($request['name']);
                        $expense->account_id = $actualAccount;

                        if ($expense->update()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Tipo de gasto ha sido modificado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($expense);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Typo de gasto.');
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

                $expense = ExpensesType::findFirst($id);

                if ($expense) {
                    $expense->setTransaction($tx);

                    if ($expense->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Tipo de gasto ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($expense);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Tipo de gasto.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Tipo de gasto no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 3 OR role_id = 7 OR role_id = 8)
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
