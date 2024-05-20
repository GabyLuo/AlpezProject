<?php

use Phalcon\Mvc\Controller;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class FilesController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];
    public function getAll ($id)
    {
        if ($this->userHasPermission()) {
            $builder = $this->modelsManager->createBuilder();
            $builder->columns([
                'j.id',
                'j.name',
                'j.description',
                'j.ext',
                'j.type',
                'round(j.size/1000000,2) as size',
                "TO_CHAR(j.created, 'yyyy/mm/dd') as date"
            ])
            ->from(['j' => 'Files'  ])
            ->where('j.repository_id = '.$id)
            ->orderBy('j.id DESC');

            $paginator = new PaginatorQueryBuilder([
                'builder' => $builder,
                'limit'   => 100000,
                'page'    => 1,
            ]);


            $this->content['info'] = $paginator->paginate()->items;
            $this->content['result'] = true;

        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function uploadFile()
    {
        $validUser = Auth::getUserData($this->config);
        $actualAccount = Auth::getUserAccount($validUser->id);
        $request = $this->request->getPost();

        if ($this->userHasPermission()) {
            // Check if the user has uploaded files
            if ($this->request->hasFiles()) {
                $upload_dir = dirname(__FILE__)  . '/../../files/'.$request['id'].'/';
                if (!is_dir($upload_dir))  {
                    mkdir($upload_dir, 0755);
                }
                $files = $this->request->getUploadedFiles();
                // Print the real file names and sizes
                foreach ($files as $file) {
                    // Print file details
                    // Move the file into the application
    
                    $type = $file->getType();
                    $size = $file->getSize();
                    $extension = $file->getExtension();
                    $name = $file->getName();

                    $file_name = md5(date('d-m-Y h:i:s').$type.$size);

                    $fileNew = new Files();
                    $fileNew->name = $name;
                    $fileNew->description = $request['description'];
                    $fileNew->ext = $extension;
                    $fileNew->size = $size;
                    $fileNew->type = $type;
                    $fileNew->repository_id = $request['id'];
                    $fileNew->account_id = $actualAccount;
    
                    if($fileNew->create()){

                        $result = $file->moveTo(
                            dirname(__FILE__)  . '/../../files/'.$request['id'].'/' . $fileNew->id
                        );
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('Se cargo el archivo.');
                    }else{
                        $this->content['result'] = false;
                        $this->content['error'] = Helpers::getErrors($fileNew);
                        $this->content['message'] = Message::error('Error al subir el archivo.');
                    }
                }
                
            }else{
                $this->content['message'] = Message::error('No se guardo ningun archivo.');
            }
        } else {
            $this->content = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent( $this->content);
        $this->response->send();
    }
    public function delete ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $info = Files::findFirst($id);

                if ($info) {
                    $info->setTransaction($tx);
                    if ($info->delete()) {
                        if(unlink(dirname(__FILE__)  . '/../../files/'.$info->repository_id.'/'.$id)){
                            $this->content['result'] = true;
                            $this->content['message'] = Message::success('El archivo ha sido eliminado.');
                            $tx->commit();
                        }else{
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el archivo.');
                            $tx->rollback();
                        }
                    } else {
                        $this->content['error'] = Helpers::getErrors($info);
                        if ($this->content['error']['message']) {
                            $this->content['message'] = Message::error($this->content['error']['message']);
                        } else {
                            $this->content['message'] = Message::error('Ha ocurrido un error al intentar eliminar el archivo.');
                        }
                        $tx->rollback();
                    }
                } else {
                    $this->content['message'] = Message::error('El archivo no existe.');
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }
    public function getFile ($id) {
        try {
            if ($this->userHasPermission()) {
                $info = Files::findFirst($id);
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Headers: *");
                readfile(dirname(__FILE__)  . '/../../files/'.$info->repository_id.'/'.$info->id);
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            $this->content['errors'] = Message::exception($e);
            $this->content['message'] = Message::error('Error al descargar archivo.');
        }
    }
    private function userHasPermission ()
    {
        return true;
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_users
                    WHERE ( role_id = 1 OR role_id = 2 OR role_id = 3 OR role_id = 7 OR role_id = 8)
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
