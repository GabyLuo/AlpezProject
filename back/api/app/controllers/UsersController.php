<?php

use Phalcon\Mvc\Controller;

class UsersController extends Controller
{
    public $content = ['result' => false, 'message' => ['title' => 'Error!', 'content' => 'Internal Server Error.']];

    public function profile ()
    {
        $content = $this->content;
        $validUser = Auth::getUserData($this->config);
        $content['user'] = null;

        if ($validUser !== null) {
            $sql = "SELECT role_id
                    FROM sys_user_roles
                    WHERE user_id = $validUser->id;";
            $currentRoles = $this->db->query($sql)->fetchAll();
            $roles = [];
            $user = Users::findFirst($validUser->id)->toArray();

            foreach ($currentRoles as $role) {
                array_push($roles, intval($role['role_id']));
            }

            if ($user) {
                $content['user']['id'] = $user['id'];
                $content['user']['nickname'] = $user['nickname'];
                $content['user']['email'] = $user['email'];
                $content['user']['roles'] = $roles;
                $content['user']['rol'] = $user['role_id'];
                $content['user']['branch'] = $user['branch_office_id'];
                $content['user']['repositories'] =  Repositories::getMenus();

                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/public/assets/uploads/profile/';

                $content['result'] = true;
            }
        } else {
            $content['message'] = Message::warning("El usuario no existe.");
        }

        $this->response->setJsonContent($content);
        $this->response->send();
    }

    public function getUsers ()
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT sys_users.id, sys_users.email, sys_users.nickname,r.name as roles,o.name as sucursal
                    FROM sys_users
                    LEFT JOIN sys_roles AS r ON r.id = sys_users.role_id
                    left join wms_branch_offices as o on o.id = sys_users.branch_office_id
                    ORDER BY email ASC;";
            $usersAux = $this->db->query($sql)->fetchAll();
           // $users = [];

           /* foreach ($usersAux as $user) {
                $roles = '';
                $sql = "SELECT r.name
                        FROM sys_user_roles AS ur
                        INNER JOIN sys_roles AS r
                        ON r.id = ur.role_id
                        WHERE ur.user_id = ".$user['id'].";";
                $rolesAux = $this->db->query($sql)->fetchAll();
                foreach ($rolesAux as $role) {
                    if ($roles == '') {
                        $roles .= $role['name'];
                    } else {
                        $roles .= '; '.$role['name'];
                    }
                }
                $user['roles'] = $roles;
                array_push($users, $user);
            }*/

            $this->content['users'] = $usersAux;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }

    public function getSellersOptions(){
        if ($this->userHasPermission()) {
            $sql = "SELECT sys_users.id, sys_users.nickname
                    FROM sys_users
                    ORDER BY sys_users.nickname ASC;";
            $query = $this->db->query($sql)->fetchAll();
            
            $options = [];

            foreach ($query as $key => $value) {
                # code...
                $options[] =[
                    'label' => $value['nickname'],
                    'value' => $value['id']
                ];
            }

            $this->content['sellers'] = $options;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
        $this->response->send();
    }
    public function getUser ($id)
    {
        if ($this->userHasPermission()) {
            $sql = "SELECT sys_users.id, sys_users.email, sys_users.nickname,sys_users.role_id,sys_users.branch_office_id as id_branch,r.name as nombre_rol, o.name as nombre_sucursal, sys_users.cluster_id, sys_users.branch_office_id as branch_id
                    FROM sys_users
                    left join sys_roles as r on r.id = sys_users.role_id
                    left join wms_branch_offices as o on o.id = sys_users.branch_office_id
                    WHERE sys_users.id = ".$id.";";
            $user = $this->db->query($sql)->fetch();
            $sql = "SELECT role_id
                    FROM sys_user_roles
                    WHERE user_id = ".$id.";";
            $roles = $this->db->query($sql)->fetchAll();
            $user['roles'] = [];
            foreach ($roles as $role) {
                array_push($user['roles'], intval($role['role_id']));
            }
            $sql = "SELECT c.id, c.name
                    FROM sys_customer_users AS cu
                    INNER JOIN sls_customers AS c
                    ON c.id = cu.customer_id
                    WHERE cu.user_id = ".$user['id'].";";
            $currentCustomer = $this->db->query($sql)->fetch();
            if ($currentCustomer) {
                $user['customerId'] = $currentCustomer['id'];
                $user['customer'] = $currentCustomer['name'];
            }
            $this->content['user'] = $user;
            $this->content['result'] = true;
        } else {
            $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
        }
        $this->response->setJsonContent($this->content);
    }

    public function getSeller () {
        $validUser = Auth::getUserInfo($this->config);
        $where = $validUser->role_id == 1 ? "" : "WHERE (su.branch_office_id = $validUser->branch_office_id or su.role_id = 1)";
        $sql = "SELECT DISTINCT su.id AS value, su.nickname AS label, branch_office_id
                FROM sys_users AS su
                JOIN sys_roles AS sr ON su.role_id = sr.id
                $where
                ORDER BY label ASC;";
                //WHERE sur.role_id in (20)
        $this->content['options'] = $this->db->query($sql)->fetchAll();
        $this->content['options2'] = $this->db->query($sql)->fetchAll();
        $this->content['result'] = true;
        $this->response->setJsonContent($this->content);
    }

    public function create ()
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $request = $this->request->getPost();
                $user = new Users();
                $user->setTransaction($tx);
                $user->email = $request['email'];
                $user->nickname = $request['nickname'];
                $user->password = password_hash($request['password'], PASSWORD_BCRYPT, ['cost' => 10]);
                $user->account_id = 1;
                $user->role_id = $request['role'] ?? null;
                $user->branch_office_id = $request['branch_id'] > 0 ? $request['branch_id'] : null;
                $user->cluster_id = $request['cluster_id'] != "" ? $request['cluster_id'] : null;

                if ($user->create()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('El usuario ha sido registrado.');
                    $this->content['userId'] = $user->id;
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($user);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar registrar el usuario.');
                    // $tx->rollback();
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            if (Message::exception($e)['code'] == 23505) {
                $this->content['error'] = Helpers::getErrors($user);
                $this->content['message'] = Message::error('El correo electrónico ingresado ya cuenta con una cuenta registrada.');
            }
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function update ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();

                $user = Users::findFirst($id);

                $request = $this->request->getPut();
                if ($user) {
                    $user->setTransaction($tx);
                    $user->email = $request['email'];
                    $user->nickname = $request['nickname'];
                    $user->role_id = $request['role_id'] ?? null;
                    $user->branch_office_id = $request['branch_id'] > 0 ? $request['branch_id'] : null;
                    if (isset($request['password']) && strlen($request['password']) > 0) {
                        $user->password = password_hash($request['password'], PASSWORD_BCRYPT, ['cost' => 10]);
                    }
                    $user->cluster_id = $request['cluster_id'] != "" ? $request['cluster_id'] : null;
                    if ($user->update()) {
                        $this->content['result'] = true;
                        $this->content['message'] = Message::success('El usuario ha sido modificado.');
                        $tx->commit();
                    } else {
                        $this->content['error'] = Helpers::getErrors($user);
                        $this->content['message'] = Message::error('Ha ocurrido un error al intentar modificar el usuario.');
                        // $tx->rollback();
                    }
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            if (Message::exception($e)['code'] == 23505) {
                $this->content['error'] = Helpers::getErrors($user);
                $this->content['message'] = Message::error('El correo electrónico ingresado ya cuenta con una cuenta registrada.');
            }
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function updateProfile ()
    {
        try {
            $tx = $this->transactions->get();

            $actualUser = Auth::getUserData($this->config);
            $user = Users::findFirst($actualUser->id);

            $request = $this->request->getPut();
            
            if ($user) {
                $user->setTransaction($tx);
                $user->email = $request['email'];
                $user->nickname = $request['nickname'];
                if (isset($request['password']) && strlen($request['password']) > 0) {
                    $user->password = password_hash($request['password'], PASSWORD_BCRYPT, ['cost' => 10]);
                }

                if ($user->update()) {
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Su perfil ha sido actualizado.');
                    $tx->commit();
                } else {
                    $this->content['error'] = Helpers::getErrors($user);
                    $this->content['message'] = Message::error('Ha ocurrido un error al intentar actualizar su perfil.');
                    // $tx->rollback();
                }
            }
        } catch (Exception $e) {
            if (Message::exception($e)['code'] == 23505) {
                $this->content['error'] = Helpers::getErrors($user);
                $this->content['message'] = Message::error('El correo electrónico ingresado ya cuenta con una cuenta registrada.');
            }
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    public function updateRoles ($id)
    {
        try {
            if ($this->userHasPermission()) {
                $tx = $this->transactions->get();
                $user = Users::findFirst($id);
                $request = $this->request->getPut();

                if ($user) {
                    $sql = "SELECT role_id
                            FROM sys_user_roles
                            WHERE user_id = $user->id;";
                    $currentRolesAux = $this->db->query($sql)->fetchAll();
                    $currentRoles = [];
                    foreach ($currentRolesAux as $role) {
                        array_push($currentRoles, intval($role['role_id']));
                    }
                    $rolesToAdd = array_diff($request['roles'], $currentRoles);
                    $rolesToDelete = array_diff($currentRoles, $request['roles']);

                    foreach ($rolesToAdd as $roleId) {
                        $role = new UserRoles();
                        $role->setTransaction($tx);
                        $role->role_id = $roleId;
                        $role->user_id = $user->id;
                        $role->create();
                    }

                    foreach ($rolesToDelete as $roleId) {
                        $role = UserRoles::findFirst("role_id = $roleId AND user_id = $user->id");
                        if ($role) {
                            $role->setTransaction($tx);
                            $role->delete();
                        }
                    }

                    $sql = "SELECT role_id
                            FROM sys_user_roles
                            WHERE user_id = $user->id;";
                    $currentRolesAux = $this->db->query($sql)->fetchAll();
                    $currentRoles = [];
                    foreach ($currentRolesAux as $role) {
                        array_push($currentRoles, intval($role['role_id']));
                    }
                    if (in_array(14, $currentRoles) || in_array(16, $currentRoles)) {
                        if (isset($request['customerId']) && is_numeric($request['customerId'])) {
                            $customerUser = CustomerUsers::findFirst("user_id = $user->id");
                            if ($customerUser) {
                                $customerUser->setTransaction($tx);
                                $customerUser->customer_id = $request['customerId'];
                                $customerUser->update();
                            } else {
                                $customerUser = new CustomerUsers();
                                $customerUser->setTransaction($tx);
                                $customerUser->user_id = $user->id;
                                $customerUser->customer_id = $request['customerId'];
                                $customerUser->create();
                            }
                        }
                    } else {
                        $customerUser = CustomerUsers::findFirst("user_id = $user->id");
                        if ($customerUser) {
                            $customerUser->delete();
                        }
                    }
                    
                    $this->content['result'] = true;
                    $this->content['message'] = Message::success('Los roles han sido actualizados exitosamente.');
                    $tx->commit();
                }
            } else {
                $this->content['message'] = Message::error('No cuenta con los permisos necesarios.');
            }
        } catch (Exception $e) {
            if (Message::exception($e)['code'] == 23505) {
                $this->content['error'] = Helpers::getErrors($user);
                $this->content['message'] = Message::error('El correo electrónico ingresado ya cuenta con una cuenta registrada.');
            }
            $this->content['errors'] = Message::exception($e);
        }

        $this->response->setJsonContent($this->content);
    }

    private function userHasPermission ()
    {
        $validUser = Auth::getUserData($this->config);
        if ($validUser && $validUser->id) {
            $sql = "SELECT id
                    FROM sys_user_roles
                    WHERE ( role_id = 1 OR role_id = 7 OR role_id = 3)
                    AND user_id = $validUser->id
                    LIMIT 1;";
            $permission = $this->db->query($sql)->fetch();
            if ($permission) {
                return true;
            }
        }
        return false;
    }
}
