<?php

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class DashboardMovementTradeController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getGpiOne ()
    {
        if ($this->userHasPermission()) {
            $sql = "select 
            sum(case  
            when movement = 'ABONO'  THEN 
            amount
            ELSE 
            -amount end) as total
            from fin_movements m
            inner join fin_outputs_types ot on ot.id = m.output_id
            inner join fin_accounts a on a.id = m.account_type_id
            where ot.name = 'SALDO INICIAL'";
            $initialBalance = $this->db->query($sql)->fetch();

            $this->content['initialBalance'] = $initialBalance['total'] ? $initialBalance['total'] : 0;

            $sql = "select 
            sum(amount) as total
            from fin_movements m
            inner join fin_outputs_types ot on ot.id = m.output_id
            inner join fin_accounts a on a.id = m.account_type_id
            where movement = 'ABONO'";
            $abono = $this->db->query($sql)->fetch();

            $this->content['abono'] = $abono['total'] ? $abono['total'] : 0;

            $sql = "select 
            sum(amount) as total
            from fin_movements m
            inner join fin_outputs_types ot on ot.id = m.output_id
            inner join fin_accounts a on a.id = m.account_type_id
            where movement = 'CARGO'";
            $charge = $this->db->query($sql)->fetch();

            $this->content['charge'] = $charge['total'] ? $charge['total'] : 0;

            $sql = "select 
            sum(case  
            when movement = 'ABONO'  THEN 
            amount
            ELSE 
            -amount end) as total
            from fin_movements m
            inner join fin_outputs_types ot on ot.id = m.output_id
            inner join fin_accounts a on a.id = m.account_type_id";
            $endBalance = $this->db->query($sql)->fetch();

            $this->content['endBalance'] = $endBalance['total'] ? $endBalance['total'] : 0;

            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function getGpiTwo ()
    {
        if ($this->userHasPermission()) {
            $sql = "select 
            sum(case  
            when movement = 'ABONO'  THEN 
            amount
            ELSE 
            -amount end) as total,
            INITCAP(a.name) as name,trim(a.code) as code
            from fin_movements m
            inner join fin_outputs_types ot on ot.id = m.output_id
            inner join fin_accounts a on a.id = m.account_type_id
            group by a.name,a.code";
            $accounts = $this->db->query($sql)->fetchAll();

            $this->content['accounts'] = $accounts;
            $this->content['result'] = true;

        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getChartOne ()
    {
        if ($this->userHasPermission()) {
            $sql = "select sum(case when movement = 'CARGO' THEN a.amount ELSE 0 END) as total,a.name,a.id  from (select 
            m.amount,
            a.name,
			a.id,
			movement   
            from fin_accounts a
            left join fin_movements m on a.id = m.account_type_id 
			left join fin_outputs_types ot on ot.id = m.output_id
			where a.code != 'GI' and a.code != 'INV'
			  ) a
            group by a.name,a.id
            order by id
			";
            $accounts = $this->db->query($sql)->fetchAll();
            $series = array();
            $names = array();
            foreach($accounts as $account){
                $series[] = (int)$account['total'];
                $names[] = $account['name'];
            }
            $this->content['names'] = $names;
            $this->content['series'] = $series;
            $this->content['result'] = true;

        } else {
           $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    } 

    public function getChartTwo ()
    {
        if ($this->userHasPermission()) {
            $sql = "select sum(case when movement = 'CARGO' THEN a.amount ELSE 0 END) as total,a.name,a.id  from (select 
            m.amount,
            ot.name,
			ot.id,
			movement   
            from fin_outputs_types ot 
			inner join fin_movements m on ot.id = m.output_id																				 
			inner join fin_accounts a on a.id = m.account_type_id																			 
			  ) a
            group by a.name,a.id";
            $accounts = $this->db->query($sql)->fetchAll();
            $series = array();
            $names = array();
            foreach($accounts as $account){
                if ((int)$account['total'] != 0) {
                    $series[] = (int)$account['total'];
                    $names[] = $account['name'];
                }
            }
            $this->content['names'] = $names;
            $this->content['series'] = $series;
            $this->content['result'] = true;

        } else {
           $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    } 
    public function getChartTwoByPayment()
    {
        if ($this->userHasPermission()) {
            $sql = "select sum(case when movement = 'ABONO' THEN a.amount ELSE 0 END) as total,a.name,a.id  from (select 
            m.amount,
            ot.name,
			ot.id,
			movement   
            from fin_outputs_types ot 
			inner join fin_movements m on ot.id = m.output_id																				 
			inner join fin_accounts a on a.id = m.account_type_id																			 
			  ) a
            group by a.name,a.id";
            $accounts = $this->db->query($sql)->fetchAll();
            $series = array();
            $names = array();
            foreach($accounts as $account){
                if ((int)$account['total'] != 0) {
                    $series[] = (int)$account['total'];
                    $names[] = $account['name'];
                }
            }
            $this->content['names'] = $names;
            $this->content['series'] = $series;
            $this->content['result'] = true;

        } else {
           $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    } 
    public function getChartThree ()
    {
        if ($this->userHasPermission()) {
            $sql = "select a.name,a.id
                FROM fin_accounts a
                ";
            $accounts = $this->db->query($sql)->fetchAll();
            $data = [];
            foreach($accounts as $index => $account){
                $data[$index]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0];
                $data[$index]['name'] = 'CARGOS ' . $account['name'];
                $data[$index]['type'] = 'column';

                $account_id = $account['id'];

                $series = array();
                $sql = "select to_char(a.expense_date, 'MM') as expense_date,sum(case when a.movement = 'CARGO' THEN a.amount ELSE 0 END) as total,a.name from (		
                    SELECT expense_date,a.name,m.movement,a.id,
                        m.amount
                    FROM fin_accounts a
                    left join fin_movements m on a.id = m.account_type_id 
                    left join fin_outputs_types ot on ot.id = m.output_id ) a
                    WHERE a.expense_date >
                        date_trunc('month', CURRENT_DATE) - INTERVAL '1 year' and a.id = {$account_id}
                    GROUP BY a.name,to_char(a.expense_date, 'MM')";
                $dataInfo = $this->db->query($sql)->fetchAll();
                foreach( $dataInfo as $row){
                    $data[$index]['series'][( (int)$row['expense_date'] - date('n'))+11] = (int)$row['total'];
                }
            }
            $size = count($data);
            $data[$size]['series'] = [0,0,0,0,0,0,0,0,0,0,0,0];
            $data[$size]['name'] = 'ABONOS';
            $data[$size]['type'] = 'line';
        
            $series = array();
            $sql = "select to_char(a.expense_date, 'MM') as expense_date,sum(case when a.movement = 'ABONO' THEN a.amount ELSE 0 END) as total from (		
                SELECT expense_date,m.movement,a.id,
                    m.amount
                FROM fin_accounts a
                left join fin_movements m on a.id = m.account_type_id 
                left join fin_outputs_types ot on ot.id = m.output_id ) a
                WHERE a.expense_date >
                    date_trunc('month', CURRENT_DATE) - INTERVAL '1 year'
                GROUP BY to_char(a.expense_date, 'MM')";
            $dataInfo = $this->db->query($sql)->fetchAll();
            foreach( $dataInfo as $row){
                if (( (int)$row['expense_date'] - date('n')) <= 0) {
                    $data[$size]['series'][( (int)$row['expense_date'] - date('n'))+11] = (int)$row['total'];
                } else {
                    $data[$size]['series'][( (int)$row['expense_date'] - date('n'))] = (int)$row['total'];
                }
            }
            foreach($accounts as $index => $account){
                $total_abonos[$index]['total_abonos'] = [0,0,0,0,0,0,0,0,0,0,0,0];
                $total_abonos[$index]['name'] = 'ABONOS ' . $account['name'];

                $account_id = $account['id'];

                $series = array();
                $sql = "select to_char(a.expense_date, 'MM') as expense_date,sum(case when a.movement = 'ABONO' THEN a.amount ELSE 0 END) as total,a.name from (		
                    SELECT expense_date,a.name,m.movement,a.id,
                        m.amount
                    FROM fin_accounts a
                    left join fin_movements m on a.id = m.account_type_id 
                    left join fin_outputs_types ot on ot.id = m.output_id ) a
                    WHERE a.expense_date >
                        date_trunc('month', CURRENT_DATE) - INTERVAL '1 year' and a.id = {$account_id}
                    GROUP BY a.name,to_char(a.expense_date, 'MM')";
                $dataInfo = $this->db->query($sql)->fetchAll();
                foreach( $dataInfo as $row){
                    if (( (int)$row['expense_date'] - date('n')) <= 0) {
                        $total_abonos[$index]['total_abonos'][( (int)$row['expense_date'] - date('n'))+11] = (int)$row['total'];
                    } else {
                        $total_abonos[$index]['total_abonos'][( (int)$row['expense_date'] - date('n'))] = (int)$row['total'];
                    }
                }
            }
            $this->content['info'] = $data;
            // $this->content['abonos'] = $data_abonos;
            $this->content['total_abonos'] = $total_abonos;
            $this->content['result'] = true;

        } else {
           $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    } 


    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 2 OR role_id = 3)
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
