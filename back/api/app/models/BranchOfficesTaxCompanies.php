<?php

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
class BranchOfficesTaxCompanies extends Model
{

    public function initialize ()
    {
        $this->setSource('wms_branch_office_tax_companies');

        $this->belongsTo('branch_office_id', 'BranchOffices', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }

}
