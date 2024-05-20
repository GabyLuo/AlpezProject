<?php

use Phalcon\Mvc\Model;
use Phalcon\Di\FactoryDefault;


class MovementsTrade extends Model
{

    public function initialize ()
    {
        $this->setSource('fin_movements');
        $this->belongsTo('account_type_id', 'AccountTrade', 'id');
        $this->belongsTo('output_id', 'OutputsTypes', 'id');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

        /*$this->hasMany(
            'id',
            'Lines',
            'category_id',
            [
                'foreignKey' => [
                    'message' => 'Hay líneas que dependen de esta categoría.',
                ]
            ]
        );*/
    }
    public static function getData($request)
    {
        $di = FactoryDefault::getDefault();
        $db = $di->get('db');

        $result = [];
        $orderName = $request['orderName'];
        $orderSor = $request['orderSor'];
        $limit = $request['limit'];
        $offset = $request['offset'];
        $month = $request['month'];
        $year = $request['year'];
        $output_type = $request['output_type'];
        $account = $request['account'];
        $movement_type = $request['movement_type'];
        $orderBy = $request['sortBy'];
        $desc = $request['descending'] === 'true' ? ' DESC ' : ' ASC '; // Al revés está bien
        $sortBy = "";
        $where = "WHERE m.id > 0";
        if (!empty($orderBy)) {
            $sortBy .= "";
            if ($orderBy == 'id') {
                $sortBy .= " ORDER BY m.id";
            }
            if ($orderBy == 'movement_type') {
                $sortBy .= " ORDER BY m.movement";
            }
            if ($orderBy == 'date') {
                $sortBy .= " ORDER BY m.expense_date";
            }
            if ($orderBy == 'account_type') {
                $sortBy .= " ORDER BY account";
            }
            if ($orderBy == 'output_type') {
                $sortBy .= " ORDER BY output";
            }
            if ($orderBy == 'description') {
                $sortBy .= " ORDER BY m.description";
            }
            if ($orderBy == 'amount') {
                $sortBy .= " ORDER BY m.amount";
            }
            if ($orderBy == 'amount_abono') {
                $sortBy .= " ORDER BY m.amount";
            }
        } else {
            $sortBy .= " ORDER BY m.id";
        }
        if ($month != 0) {
            $where .= " and (EXTRACT( MONTH FROM m.expense_date) =  {$month})";
        }
        if ($year != 0) {
            $where .= " and (EXTRACT( MONTH FROM m.expense_date) =  {$year})";
        }
        if ($account != 0) {
            $where .= " and (a.id = {$account})";
        }
        if ($output_type != 0) {
            $where .= " and (ot.id = {$output_type})";
        }
        if ($movement_type != null) {
            $where .= " and (movement = '{$movement_type}' or '{$movement_type}'  = 'all')";
        }
        // $where = "where (EXTRACT( YEAR FROM m.expense_date) =  {$year} or {$year} = 0) 
        // and (EXTRACT( MONTH FROM m.expense_date) =  {$month} or {$month} = 0)
        // and (ot.id = {$output_type} or {$output_type}  = 0)
        // and (a.id = {$account} or {$account}  = 0)
        // and (movement = '{$movement_type}' or '{$movement_type}'  = 'all')
        // ";

        $query =  "SELECT count(*) as result,sum(case  
        when movement = 'CARGO'  THEN 
        -amount end) as amount,
            sum(case  
        when movement = 'ABONO'  THEN 
        amount end) as amount_abono
        FROM fin_movements AS m
        INNER JOIN fin_accounts AS a ON m.account_type_id = a.id
        INNER JOIN fin_outputs_types AS ot ON m.output_id = ot.id
        {$where}
        ";
       
       $result['count'] = $db->query($query)->fetch();
        
        $query =  "SELECT m.id, case when m.movement = 'ABONO' then m.amount end as amount_abono, case when m.movement = 'CARGO' then m.amount end as amount, TO_CHAR(m.expense_date :: DATE, 'dd/mm/yyyy') AS date, m.movement, m.description, a.name AS account, ot.name AS output
        FROM fin_movements AS m
        INNER JOIN fin_accounts AS a ON m.account_type_id = a.id
        INNER JOIN fin_outputs_types AS ot ON m.output_id = ot.id

        {$where}
        {$sortBy} {$desc}
        limit   {$limit} 
        offset  {$offset} 
        ";
       $result['data'] = $db->query($query)->fetchAll();
       array_push($result['data'], array('account' => '','date' => '','description'=>'SUBTOTALES','id'=>'','movement'=>'','output'=>'','amount' => $result['count']['amount'], 'amount_abono' =>  $result['count']['amount_abono'],));
        return  $result ;
    }
}
