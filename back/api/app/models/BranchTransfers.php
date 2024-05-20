<?php

use Phalcon\Mvc\Model;

class BranchTransfers extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_branch_transfers');

        $this->belongsTo('transaction_id', 'Transactions', 'id');
        $this->belongsTo('operator_id', 'Operators', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }
}
