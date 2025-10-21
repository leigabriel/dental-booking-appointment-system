<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Auth extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UserModel');
        $this->call->library('Form_validation');
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
                redirect('/');
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
        $role = $this->session->userdata('role');
        if ($this->session->userdata('is_logged_in')) {
            $this->_redirect_by_role($role);
        }
    }

    // PUBLIC ROUTES

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

        $identifier = $data['username'] ?? ''; // Renamed 'username' to be the identifier field

        // 1. Set Validation Rules: Only ensure the identifier and password are present.
        $this->form_validation
            ->name('username|Username or Email')->required() // Updated label to reflect dual use
            ->name('password|Password')->required();

        if ($this->form_validation->run()) {

            // 2. Fetch User by Username OR Email
            $user = null;

            // Check if identifier looks like an email
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $user = $this->UserModel->filter(['email' => $identifier])->get();
            }

            // If not found by email, or if it's not an email, try finding by username
            if (!$user) {
                $user = $this->UserModel->find_by_username($identifier);
            }

            if ($user && password_verify($data['password'], $user['password'])) {

                // 3. Success: Set Session Data and Redirect
                $session_data = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'is_logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);

                // Redirect based on the role stored in the database
                $this->_redirect_by_role($user['role']);
            } else {
                // Failure: Invalid Credentials
                $data['error'] = 'Invalid username/email or password.';
                $data['username'] = $identifier;
                $this->call->view('auth/login', $data);
            }
        } else {
            // 4. Validation Failed
            $data['error'] = $this->form_validation->errors();
            $data['username'] = $identifier;
            $this->call->view('auth/login', $data);
        }
    }

    public function register_submit()
    {
        $this->_check_logged_in();
        $data = $this->io->post();

        // 1. Set Validation Rules
        $this->form_validation
            // NEW VALIDATION RULES
            ->name('full_name|Full Name')->required()->valid_name() // Check for letters and spaces only
            ->name('email|Email')->required()->valid_email()->is_unique('users', 'email', $data['email']) // Added unique check for email
            ->name('username|Username')->required()->alpha_numeric()->is_unique('users', 'username', $data['username'])
            ->name('password|Password')->required()->min_length(6)
            ->name('confirm_password|Confirm Password')->required()->matches('password');

        // Validate role remains the same
        $this->form_validation->name('role|Role')->required()->in_list('user,staff,admin');

        if ($this->form_validation->run()) {

            // 2. Prepare Data and Save
            $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

            $final_role = $this->io->post('role');

            $new_user_data = [
                'full_name' => $this->io->post('full_name'),
                'email'     => $this->io->post('email'),
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
            $data['full_name'] = $this->io->post('full_name');
            $data['email'] = $this->io->post('email');

            $this->call->view('auth/register', $data);
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }

    public function profile()
    {
        // Redirect if not logged in
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
        }

        $this->call->model(['AppointmentModel', 'DoctorModel', 'ServiceModel']);

        $user_id = $this->session->userdata('user_id');
        $data['user'] = $this->UserModel->find($user_id);

        // Fetch only appointments belonging to the current user
        $data['appointments'] = $this->AppointmentModel->filter(['user_id' => $user_id])->get_all();

        // Fetch lookup data for display (doctors and services)
        $data['doctors'] = array_column($this->DoctorModel->all() ?? [], null, 'id');
        $data['services'] = array_column($this->ServiceModel->all() ?? [], null, 'id');

        $this->call->view('auth/profile', $data);
    }

    /**
     * Handles profile update submission
     */
    public function profile_edit_submit()
    {
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
        }

        $user_id = $this->session->userdata('user_id');
        $data = $this->io->post();

        // 1. Set Validation Rules
        $this->form_validation
            ->name('full_name|Full Name')->required()->valid_name()
            ->name('email|Email')->required()->valid_email();

        if ($this->form_validation->run()) {

            $update_data = [
                'full_name' => $this->io->post('full_name'),
                'email' => $this->io->post('email')
            ];

            if (!empty($this->io->post('new_password'))) {
                // If a new password is provided, validate it and hash it
                $this->form_validation
                    ->name('new_password|New Password')->required()->min_length(6)
                    ->name('confirm_new_password|Confirm Password')->required()->matches('new_password')->run();

                if ($this->form_validation->run()) {
                    $update_data['password'] = password_hash($this->io->post('new_password'), PASSWORD_BCRYPT);
                } else {
                    $this->session->set_flashdata('error_message', $this->form_validation->errors());
                    redirect('profile');
                }
            }

            $this->UserModel->update($user_id, $update_data);
            $this->session->set_flashdata('success_message', 'Profile updated successfully!');
        } else {
            $this->session->set_flashdata('error_message', $this->form_validation->errors());
        }
        redirect('profile');
    }

    /**
     * Handles account deletion (Delete).
     */
    public function profile_delete()
    {
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
        }

        $user_id = $this->session->userdata('user_id');
        $this->UserModel->delete($user_id);

        // Destroy the session after deletion
        $this->session->sess_destroy();
        $this->session->set_flashdata('success_message', 'Your account has been deleted.');

        redirect('/');
    }
}