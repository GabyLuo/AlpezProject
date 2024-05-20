<?php

use Phalcon\Mvc\Controller;

class CurrenciesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getCurrencies ()
    {
        if ($this->userHasPermission()) {
            $this->content['currencies'] = Currencies::find(['order' => 'id ASC']);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getCurrency ($id)
    {
        if ($this->userHasPermission()) {
            $this->content['currency'] = Currencies::findFirst($id);
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id as value, name AS label, name FROM acc_currency_types ORDER BY name ASC;";
        // $types = $this->db->query($sql)->fetchAll();

        // $options = [];
        // foreach ($types as $type) {
        //     $options[] = [
        //         'id' => $type['id'],
        //         'label' => $type['name'],
        //         'code' => $type['code']
        //     ];
        // }
        // $this->content['options'] = $options;
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

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                $actualCategory = Currencies::findFirst("code = '".$request['code']."'");

                if ($actualCategory) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un tipo de moneda con el mismo código.');
                } else {
                    $actualCategory = Currencies::findFirst("name = '".$request['name']."'");
                    if ($actualCategory) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un tipo de moneda con el mismo nombre.');
                    } else {
                        $currency = new Currencies();
                        $currency->setTransaction($tx);
                        $currency->name = strtoupper($request['name']);
                        $currency->code = strtoupper($request['code']);
                        $currency->account_id = $actualAccount;

                        if ($currency->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El tipo de moneda ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($currency);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear el tipo de moneda.');
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

                $currency = Currencies::findFirst($id);

                $request = $this->request->getPut();

                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);
                $actualCategory = Currencies::findFirst("code = '".$request['code']."' AND id <> '".$id."'");

                if ($actualCategory) {
                    $this->content['message'] = Message::success('Ya se encuentra registrado un tipo de moneda con el mismo código.');
                } else {
                    $actualCategory = Currencies::findFirst("name = '".$request['name']."' AND id <> '".$id."'");
                    if ($actualCategory) {
                        $this->content['message'] = Message::success('Ya se encuentra registrado un tipo de moneda con el mismo nombre.');
                    } else {
                        if ($currency) {
                            $currency->setTransaction($tx);
                            $currency->name = strtoupper($request['name']);
                            $currency->code = strtoupper($request['code']);
                            $currency->account_id = $actualAccount;

                            if ($currency->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El tipo de moneda ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($currency);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el tipo de moneda.');
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

                $currency = Currencies::findFirst($id);

                if ($currency) {
                    $currency->setTransaction($tx);

                    if ($currency->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El tipo de moneda ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($currency);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el tipo de moneda.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El tipo de moneda no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3)
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
