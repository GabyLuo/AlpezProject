<?php

use Phalcon\Mvc\Controller;

class AccountTradeController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getAccounts ()
    {
        if ($this->userHasPermission()) {
            $this->content['accounts'] = AccountTrade::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getAccount ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['account'] = AccountTrade::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM fin_accounts ORDER BY name ASC;";
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
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $Account = Auth::getUserAccount($validUser->id);
                $actualAccount = AccountTrade::findFirst("code = '".$request['code']."'");

                if ($actualAccount) {
                    $this->content['message'] = Message::success('Ya se encuentra registrada una cuenta con el mismo código.');
                } else {
                    $actualAccount = AccountTrade::findFirst("name = '".$request['name']."'");
                    if ($actualAccount) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una cuenta con el mismo nombre.');
                    } else {
                        $account = new AccountTrade();
                        $account->setTransaction($tx);
                        $account->name = strtoupper($request['name']);
                        $account->code = strtoupper($request['code']);
                        $account->account_id = $Account;

                        if ($account->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('La cuenta ha sido creada.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($account);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la cuenta.');
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
                $account = AccountTrade::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $Account = Auth::getUserAccount($validUser->id);
                $actualAccount = AccountTrade::findFirst("code = '".$request['code']."' AND id <> '".$id."'");

                if ($actualAccount) {
                    $this->content['message'] = Message::success('Ya se encuentra registrada una cuenta con el mismo código.');
                } else {
                    $actualAccount = AccountTrade::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualAccount) {
                        $this->content['message'] = Message::success('Ya se encuentra registrada una cuenta con el mismo nombre.');
                    } else {
                        if ($account) {
                            $account->setTransaction($tx);
                            $account->name = strtoupper($request['name']);
                            $account->code = strtoupper($request['code']);
                            $account->account_id = $Account;

                            if ($account->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('La cuenta ha sido modificada.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($account);
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

                $account = AccountTrade::findFirst($id);

                if ($account) {
                    $account->setTransaction($tx);

                    if ($account->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('La cuenta ha sido eliminada.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($account);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar la cuenta.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('La cuenta no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 24)
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
