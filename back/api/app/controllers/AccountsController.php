<?php

use Phalcon\Mvc\Controller;

class AccountsController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function getAccounts ()
    {
        $this->content['accounts'] = Accounts::find(['order' => 'id ASC']);
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getAccount ($id)
    {
        $this->content['account'] = Accounts::findFirst($id);
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function getOptions () {
        $sql = "SELECT id, name FROM sys_accounts ORDER BY id ASC;";
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
}
