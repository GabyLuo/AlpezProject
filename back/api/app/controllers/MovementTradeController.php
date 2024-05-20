<?php

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MovementTradeController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getMovements ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT m.id, m.amount, TO_CHAR(m.expense_date :: DATE, 'dd/mm/yyyy') AS date, m.movement, m.description, a.name AS account, ot.name AS output
            FROM fin_movements AS m
            INNER JOIN fin_accounts AS a ON m.account_type_id = a.id
            INNER JOIN fin_outputs_types AS ot ON m.output_id = ot.id";
            $movements = $this->db->query($sql)->fetchAll();

            $this->content['movements'] = $movements;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function getMovementsPost()
    {
        if ($this->userHasPermission()) {

            $request = $this->validData ($this->request->getPost());
            $movements = MovementsTrade::getData($request);
            $this->content['movements'] = $movements['data'];
            $this->content['count'] = $movements['count']["result"];
            $this->content['total_abono'] = $movements['count']["amount_abono"];
            $this->content['total_cargo'] = $movements['count']["amount"];
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function validData ($request){

        $request['output_type'] = $this->filter->sanitize(!empty($request['output_type']) ? $request['output_type'] : NULL,'int');

        $request['year'] = $this->filter->sanitize(!empty($request['year']) ? $request['year'] : NULL,'int');

        $request['month'] = $this->filter->sanitize(!empty($request['month']) ? $request['month'] : NULL,'int');

        $request['account'] = $this->filter->sanitize(!empty($request['account']) ? $request['account'] : NULL,'int');

        $request['movement_type'] = $this->filter->sanitize(!empty($request['movement_type']) ? $request['movement_type'] : NULL,'alnum');
        
        $request['limit'] = $this->filter->sanitize(!empty($request['rowsPerPage']) ? $request['rowsPerPage'] : NULL,'int');
        if($request['limit']  === 0 ){
            $request['limit'] = 100000;
        }
        $request['offset'] = $this->filter->sanitize(!empty($request['page']) ? $request['page'] : NULL,'int')-1;

        $request['orderName'] = $this->filter->sanitize(!empty($request['sortBy']) ? $request['sortBy'] : NULL,'alnum');
        if($request['rowsNumber']){
            $request['orderSor']= 'desc';
        }else{
            $request['orderSor']= 'asc';
        }

        return $request;

    }
    public function getMovement ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT m.id, m.amount, TO_CHAR(m.expense_date :: DATE, 'dd/mm/yyyy') AS date, m.movement, m.description, a.name AS account_name, a.id AS account_id, 
            ot.name AS output_name, ot.id AS output_id
            FROM fin_movements AS m
            INNER JOIN fin_accounts AS a ON m.account_type_id = a.id
            INNER JOIN fin_outputs_types AS ot ON m.output_id = ot.id
            WHERE m.id = $id";
            $movements = $this->db->query($sql)->fetch();

            $this->content['movement'] = $movements;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM fin_movements ORDER BY name ASC;";
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
    public function getOptionsYears () {
        $sql = "SELECT EXTRACT( YEAR FROM expense_date) as display , EXTRACT( YEAR FROM expense_date) as value FROM fin_movements GROUP BY EXTRACT( YEAR FROM expense_date)  ORDER BY EXTRACT( YEAR FROM expense_date) ASC;";
        $types = $this->db->query($sql)->fetchAll();

        $options = [];
        foreach ($types as $type) {
            $options[] = [
                'value' => $type['value'],
                'label' => $type['display']
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

                $account = new MovementsTrade();
                $account->setTransaction($tx);
                $account->account_type_id = $request['account_id'];
                $account->output_id = $request['output_id'];
                $account->amount = $request['amount'];
                $account->expense_date = $request['date'];
                $account->movement = strtoupper($request['movement_type']);
                $account->description = strtoupper($request['description']);
                $account->account_id = $actualAccount;

                if ($account->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El movimiento ha sido creado.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($account);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar crear la cuenta.');
                    // $tx->rollback();
                }
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
                $account = MovementsTrade::findFirst($id);
                $request = $this->request->getPut();
                $validUser = Auth::getUserData($this->config);
                $actualAccount = Auth::getUserAccount($validUser->id);

                if ($account) {
                    $account->setTransaction($tx);
                    $account->account_type_id = $request['account_id'];
                    $account->output_id = $request['output_id'];
                    $account->amount = $request['amount'];
                    $account->expense_date = $request['date'];
                    $account->movement = strtoupper($request['movement_type']);
                    $account->description = strtoupper($request['description']);
                    $account->account_id = $actualAccount;

                    if ($account->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El movimiento ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($account);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar la categoría.');
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

                $account = MovementsTrade::findFirst($id);

                if ($account) {
                    $account->setTransaction($tx);

                    if ($account->delete()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El movimiento ha sido eliminado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($account);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el movimiento.');
                        }
                        // $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El movimiento no existe.');
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
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3 OR role_id = 24 OR role_id = 20) 
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
