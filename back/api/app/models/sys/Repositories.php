<?php

use Phalcon\Mvc\Model;

class Repositories extends Model
{

    public function initialize ()
    {
        $this->setSource('sys_repositories');
        // $this->belongsTo('id', 'Lines', 'category_id');
        $this->belongsTo('account_id', 'Accounts', 'id');
        $this->belongsTo('created_by', 'Users', 'id');
        $this->belongsTo('updated_by', 'Users', 'id');

    }


    public static function getMenus ()
    {
        $parents = Repositories::find(['order' => 'sequence ASC', "parent_id is null"]);
        
        $info = array();
        if($parents){
            foreach($parents as $index => $parent){
                $childs = Repositories::find(['order' => 'sequence ASC', "parent_id =  {$parent->id}"]);
                $info[$index]['general'] = $parent;
                $info[$index]['childs'] = $childs;
            }
        }
        return  $info;
    }
}
