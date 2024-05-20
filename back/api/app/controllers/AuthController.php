<?php

use Phalcon\Mvc\Controller;

class AuthController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function login ()
    {
        try {
            $request = $this->request->getPost();

            if (isset($request['email']) && isset($request['password'])) {
                sleep(0.5);
                $userId = Auth::getUserId(strtolower($request['email']), $request['password']);
                
                if ($userId > -1) {
                    $data = ['id' => $userId];
                    $jwt = Auth::createToken($this->request->getHttpHost(), $data, $this->config->jwtkey);
                    $this->content['jwt'] = $jwt;
                    $this->content['result'] = true;
                } else {
                    $this->content['message'] = Message::warning('El correo electrónico o la contraseña no coinciden con ningún usuario.');
                }
            } else {
                $this->content['message'] = Message::warning('Por favor escriba la cuenta de correo electrónico y contraseña.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }
        
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
}
