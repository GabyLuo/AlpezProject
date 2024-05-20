<?php

use Phalcon\Mvc\Controller;

class ExchangesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];
    
    public function getExchanges ($pt = 0)
    {
        //$content = $this->content;
        if ($this->userHasPermission()) {
            // $this->content['exchanges'] = Exchanges::find(['order' => 'id ASC']);
            // $this->content['result'] = true;
             $sql = "SELECT e.id, c.name as currency_name, c.code as currency_code, e.current_value, TO_CHAR(e.exchange_date :: DATE, 'dd/mm/yyyy') as date
                     FROM acc_exchange_types AS e
                     INNER JOIN acc_currency_types AS c
                     ON e.currency_id = c.id";
            
              $data = $this->db->query($sql)->fetchAll();
             $this->content['exchanges'] = $data;
             $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    
    public function getExchange ($id)
    {
        if ($this->userHasPermission()) {
            // $this->content['exchange'] = Exchanges::findFirst([$id]);
            // $this->content['result'] = true;
            $sql = "SELECT e.id, c.name as label, c.id as value, e.current_value, TO_CHAR(e.exchange_date :: DATE, 'dd/mm/yyyy') as date
            FROM acc_exchange_types AS e
            INNER JOIN acc_currency_types AS c
            ON e.currency_id = c.id";
   
     $data = $this->db->query($sql)->fetch();
    $this->content['exchange'] = $data;
    $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getExchangeFromCurrency ($currency_id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT e.id, c.name as label, c.id as value, e.current_value, TO_CHAR(e.exchange_date :: DATE, 'dd/mm/yyyy') as date
            FROM acc_exchange_types AS e
            INNER JOIN acc_currency_types AS c
            ON e.currency_id = c.id
            WHERE c.id = {$currency_id} and e.exchange_date <= '".$date."'
            order by e.exchange_date DESC";
   
            $data = $this->db->query($sql)->fetch();
            $this->content['exchange'] = $data;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getExchangePO ($currency_id, $order_id)
    {
        if ($this->userHasPermission()) {
            $order = PurchaseOrders::findFirst($order_id);
            $sql = "SELECT e.id, c.name as label, c.id as value, e.current_value, TO_CHAR(e.exchange_date :: DATE, 'dd/mm/yyyy') as date
                FROM acc_exchange_types AS e
                INNER JOIN acc_currency_types AS c
                ON e.currency_id = c.id
                WHERE c.id = {$currency_id} and e.exchange_date <= '".$order->requested_date."'
                order by e.exchange_date DESC";
   
            $data = $this->db->query($sql)->fetch();
            $this->content['exchange'] = $data;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getOptions () {
        $sql = "SELECT id, current_value AS value, currecy_id AS currency
                FROM acc_exchange_types
                ORDER BY name ASC;";
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getOptionsByExchangeId ($exchangeId) {
        $options = [];
        if (is_numeric($exchangeId)) {
            $sql = "SELECT e.id , c.name AS label
                    FROM acc_exchange_types AS e
                    INNER JOIN acc_currency_types AS c
                    ON e.currency_id = c.id
                    WHERE currency_id = $exchangeId
                    ORDER BY name ASC;";
            $options = $this->db->query($sql)->fetchAll();
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

                $actualExchange = Exchanges::findFirst("currency_id = '".$request['currency_id']."' AND current_value = '".$request['current_value']."'");

                if ($actualExchange) {
                    $this->content['message'] = Message::error('Ya se encuentra registrado un Tipo de Cambio con el mismo Tipo de Moneda y Valor.');
                } else {
                    $actualExchange = Exchanges::findFirst("currency_id = '".$request['currency_id']."' AND exchange_date = '".$request['date']."'");
                    if ($actualExchange) {
                        $this->content['message'] = Message::error('Ya se encuentra registrado un Tipo de Cambio con el mismo Tipo de Moneda y Fecha.');
                    } else {
                        $exchange = new Exchanges();
                        $exchange->setTransaction($tx);
                        $exchange->exchange_date = $request['date'];
                        $exchange->current_value = $request['current_value'];
                        $exchange->currency_id = $request['currency_id'];

                        if ($exchange->create()) {
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El Tipo de Cambio ha sido creado.');
                            $tx->commit();
                        } else {
                            $this->content['error'] = Helpers::getErrors($exchange);
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la línea.');
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

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPut();

                $actualExchange = Exchanges::findFirst("currency_id = '".$request['currency_id']."' AND current_value = '".$request['current_value']."'");

                if ($actualExchange) {
                    $this->content['message'] = Message::error('Ya se encuentra registrado un Tipo de Cambio con el mismo Tipo de Moneda y Valor.');
                } else {
                    $actualExchange = Exchanges::findFirst("currency_id = '".$request['currency_id']."' AND exchange_date = '".$request['date']."'");
                    if ($actualExchange) {
                        $this->content['message'] = Message::error('Ya se encuentra registrado un Tipo de Cambio con el mismo Tipo de Moneda y Fecha.');
                    } else {
                        $exchange = Exchanges::findFirst($id);

                        if ($exchange) {
                            $exchange->setTransaction($tx);
                            $exchange->exchange_date = $request['date'];
                            $exchange->current_value = $request['current_value'];
                            $exchange->currency_id = $request['currency_id'];

                            if ($exchange->update()) {
                                $this->content['result'] = true;
                                $this->content['message'] = Message::success('El Tipo de Cambio ha sido modificado.');
                                $tx->commit();
                            } else {
                                $this->content['error'] = Helpers::getErrors($exchange);
                                $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el Tipo de Cambio.');
                                // $tx->rollback();
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

                $exchange = Exchanges::findFirst($id);

                if ($exchange) {
                    $exchange->setTransaction($tx);

                    if ($exchange->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El Tipo de Cambio ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($exchange);
                        if ($this->content['error'][1]) {
                            $this->content['message'] = Message::error($this->content['error'][1]);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el Tipo de Cambio.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El Tipo de Cambio no existe.');
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
