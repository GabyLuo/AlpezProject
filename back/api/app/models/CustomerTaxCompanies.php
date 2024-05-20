<?php

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
class CustomerTaxCompanies extends Model
{

    public function initialize ()
    {
        $this->setSource('sls_customer_tax_companies');

        $this->belongsTo('customer_id', 'Customers', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');
    }

    function validation() {
        $validator = new Validation();

        $validator->add(
            'rfc',
            new Uniqueness([
                   'message' => 'Este RFC ya se encuentra registrado.',
               ]
            )
        );

        return $this->validate($validator);
    }
}
