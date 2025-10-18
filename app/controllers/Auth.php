<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Auth extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // CRITICAL FIX: Explicitly load the model and validation library here.
        // This ensures $this->UserModel and $this->form_validation are available 
        // immediately in all methods that run afterward.
        $this->call->model('UserModel');
        $this->call->library('Form_validation');

        // NOTE: We don't need to load Session here because it's in autoload.php
        // but we must ensure dependencies required by the parent's __get method are met.
    }

    /**
     * Redirects the user to the appropriate dashboard based on their role.
     * @param string $role The user's role ('admin', 'staff', or 'user').
     * @return void
     */
    private function _redirect_by_role($role)
    {
        switch ($role) {
            case 'admin':
                redirect('admin/dashboard');
                break;
            case 'staff':
                redirect('staff/dashboard');
                break;
            case 'user':
            default:
                redirect('/'); // Default user landing page
                break;
        }
    }

    /**
     * Checks if the user is already logged in and redirects them.
     *
     * @return void
     */
    private function _check_logged_in()
    {
        // FIX: The session object relies on being loaded in autoload.php. 
        // We will now rely on that global instance.
        $role = $this->session->userdata('role');
        if ($this->session->userdata('is_logged_in')) {
            $this->_redirect_by_role($role);
        }
    }

    // --- PUBLIC ROUTES ---

    public function login()
    {
        $this->_check_logged_in();
        $this->call->view('auth/login');
    }

    public function register()
    {
        $this->_check_logged_in();
        $this->call->view('auth/register');
    }

    public function login_submit()
    {
        $this->_check_logged_in();
        $data = $this->io->post();

        // 1. Set Validation Rules
        $this->form_validation
            ->name('username|Username')->required()->alpha_numeric()
            ->name('password|Password')->required();

        if ($this->form_validation->run()) {

            // 2. Fetch User and Verify Password
            $user = $this->UserModel->find_by_username($data['username']);

            if ($user && password_verify($data['password'], $user['password'])) {

                // 3. Success: Set Session Data and Redirect
                $session_data = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'], // Get role from database
                    'is_logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);

                // Redirect based on the role stored in the database
                $this->_redirect_by_role($user['role']);
            } else {
                // Failure: Invalid Credentials
                $data['error'] = 'Invalid username or password.';
                $data['username'] = $this->io->post('username');
                $this->call->view('auth/login', $data);
            }
        } else {
            // 4. Validation Failed (e.g., empty fields)
            $data['error'] = $this->form_validation->errors();
            $data['username'] = $this->io->post('username');
            $this->call->view('auth/login', $data);
        }
    }

    public function register_submit()
    {
        $this->_check_logged_in();
        $data = $this->io->post();

        // 1. Set Validation Rules
        $this->form_validation
            ->name('username|Username')->required()->alpha_numeric()->is_unique('users', 'username', $data['username'])
            ->name('password|Password')->required()->min_length(6)
            ->name('confirm_password|Confirm Password')->required()->matches('password');

        // Validate the submitted role against the allowed ENUM values.
        $this->form_validation->name('role|Role')->required()->in_list('user,staff,admin');

        if ($this->form_validation->run()) {

            // 2. Prepare Data and Save
            $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

            $final_role = $this->io->post('role');

            $new_user_data = [
                'username' => $data['username'],
                'password' => $hashed_password,
                'role' => $final_role
            ];

            if ($this->UserModel->create_user($new_user_data)) {
                $this->session->set_flashdata('success_message', 'Registration successful! You may now login.');
                redirect('login');
            } else {
                $this->session->set_flashdata('error_message', 'A database error occurred during registration.');
                redirect('register');
            }
        } else {
            // 3. Validation Failed
            $data['errors'] = $this->form_validation->get_errors();
            $data['username'] = $this->io->post('username');
            $this->call->view('auth/register', $data);
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }
    
}
