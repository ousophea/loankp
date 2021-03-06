<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of users
 *
 * @author sochy.choeun
 */
class users extends CI_Controller {

    //put your code here
    private $data;

    function __construct() {
        parent::__construct();
        $this->load->model(array('s_users', 'd_users', 'd_roles', 's_roles'));
        $this->data['dbf'] = new dbf();
        $this->data['title'] = NULL;
        $this->data['data'] = NULL;
    }

    function index() {
        redirect('users/manage');
    }

    function login() {
        try {
            if (is_login()) {
                redirect('panel/manage');
            }
            $dbf = $this->data['dbf'];
            $this->form_validation->set_error_delimiters('<div class="alert alert-error">', '</div>');
            $this->form_validation->set_rules($dbf->getF_username(), 'Username', 'required');
            $this->form_validation->set_rules($dbf->getF_password(), 'Password', 'required');
            if ($this->form_validation->run() == FALSE)
                $this->load->view(Variables::$layout_login, $this->data);
            else {
                $user = new s_users();
                $user->setUsername($this->input->post($dbf->getF_username()));
                $user->setPassword(md5($this->input->post($dbf->getF_password())));
                if ($this->d_users->getLogin($user)) {
                    // create session roleName and userName
                    $this->session->set_userdata($user->getF_username(), $user->getUsername());
                    $this->session->set_userdata('use_id',$this->d_users->getUserId($user->getUsername()));
                    $this->session->set_userdata('use_bra_id',$this->d_users->getUserBraId($user->getUsername()));
                    $role = new d_roles();
                    $roles = $role->setRoleByUsername($user, $user->getF_rol_name());
                    $this->session->set_userdata($user->getF_rol_name(), $roles->getRole());
                    $this->session->set_userdata('gro_id',$this->d_roles->getRoleId());
					$this->session->set_userdata('bra_id',1);
//                    if($this->input->post('remember')){
//                        
//                    }
//                    if ($this->input->post('remember')) {
//                        $cookie = array(
//                            $user->getF_username() => $user->getUsername(),
//                            'value' => "aaaa",
//                            'expire' => '86500',
//                            'path' => '/',
//                            'secure' => FALSE
//                        );
//
//                        $this->input->set_cookie($cookie);
//                   }
//                    $this->input->set_cookie($cookie);
                    redirect('panel/manage');
                } else {
                    $this->data['login'] = '<div class="alert alert-error">Username and Password incorrect.</div>';
                    $this->load->view(Variables::$layout_login, $this->data);
                }
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * @param void $name register function is use to add a new user in into system, and we can access via url
     * @example base_url/users/register
     */
    function register() {
        try {
            allows(array(Setting::$role0, Setting::$role1));
            $this->data['title'] = "Create a new user";
            $dbf = $this->data['dbf'];
            $this->form_validation->set_rules($dbf->getF_Username(), 'Username', 'required|min_length[5]|max_length[30]|is_unique[' . $dbf->getT_users() . '.' . $dbf->getF_username() . ']');
            $this->form_validation->set_rules($dbf->getF_password(), 'Password', 'required|min_length[5]|max_length[12]');
            $this->form_validation->set_rules($dbf->getF_password() . 'c', 'Password confirmation', 'required|matches[' . $dbf->getF_password() . ']');
            $this->form_validation->set_rules($dbf->getF_rol_id(), 'Role', 'required');
            $this->form_validation->set_message('is_unique', 'Username aready exist.');

            if ($this->form_validation->run() == FALSE) {
                $obj = new s_roles();
                $this->data['roles'] = $this->d_roles->getAllRoles($obj);
                $this->load->view(Variables::$layout_main, $this->data);
            } else {
                $s_user_obj = new s_users();
                $s_user_obj->setUsername($this->input->post($dbf->getF_username()));
                $s_user_obj->setRole($this->input->post($dbf->getF_rol_id()));
                $s_user_obj->setPassword(md5($this->input->post($dbf->getF_password())));
                if ($this->d_users->getRegister($s_user_obj)) {
                    $this->session->set_flashdata('success', 'User has been saved');
                    redirect('users/manage');
                } else {
                    
                }
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * edit user
     */
    function edit() {

        try {
            allows(array(Setting::$role0, Setting::$role1));
            $this->data['title'] = 'Edit user';
            $dbf = new dbf();
            $dbf = $this->data['dbf'];
            $this->form_validation->set_rules($dbf->getF_user_rol_id(), 'Role', 'required');
            if ($this->form_validation->run() == FALSE) {
                $obj = new s_roles();
                $this->data['roles'] = $this->d_roles->getAllRoles($obj);
                $this->data['data'] = array('user' => $this->d_users->getUserById($this->uri->segment(3)));
                $this->load->view(Variables::$layout_main, $this->data);
            } else {
                if ($this->d_users->editUserById())
                    $this->session->set_flashdata('success', 'User has been updated');
                else {
                    $this->session->set_flashdata('error', 'User could not be updated');
                }
                redirect('users/manage');
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * @param Destroy session $name This function will destroy all session of website
     */
    public function signout() {
        $this->session->sess_destroy();
        redirect('users/login');
    }

    /**
     * 
     * @param String $url
     * @param redirecting $name This function will redirect follow $url in case it could not session of username
     */
    private function check_session() {
        $session = $this->session->userdata($this->data['dbf']->getF_username());
        $isSession = strtolower($session) == "admin" || strtolower($session) == "superadmin";
        if (!$session) {
            redirect('users/login');
        } else if ($isSession) {
            //redirect('users/no_auth');
        }
    }

    function no_auth() {
        $this->data['title'] = "No permission";
        $this->load->view(Variables::$layout_main, $this->data);
    }

    function manage() {
        allows(array(Setting::$role0, Setting::$role1));
        $this->data['title'] = 'Manage users';
        $this->data['data'] = array('users' => $this->d_users->findAllUsers());

        $this->load->view(Variables::$layout_main, $this->data);
    }

    function delete() {
        allows(array(Setting::$role0, Setting::$role1));
        if ($this->d_users->deleteUserById()) {
            $this->session->set_flashdata('success', 'User has been deleted. Note: The current user is not allow to delete.');
            redirect('users/manage');
        } else {
            $this->session->set_flashdata('error', 'User could not deleted');
            redirect('users/manage');
        }
    }

    function changepassword() {
        allows(array(Setting::$role0, Setting::$role1));
        $this->data['title'] = 'Change password';
        $dbf = new dbf();
        $this->form_validation->set_rules($dbf->getF_password(), 'Password', 'required|min_length[5]|max_length[12]');
        $this->form_validation->set_rules($dbf->getF_password() . 'c', 'Password confirmation', 'required|matches[' . $dbf->getF_password() . ']');
        if ($this->form_validation->run() == FALSE) {
            $this->load->view(Variables::$layout_main, $this->data);
        } else {
            if ($this->d_users->changepassword())
                $this->session->set_flashdata('success', 'Password has been changed');
            else {
                $this->session->set_flashdata('error', 'Password could not be changed');
            }
            redirect('users/manage');
        }
    }
    
    function findUserByRole($role){
        
        
    }

}

?>
