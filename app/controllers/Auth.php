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

    // Redirect user based on role
    private function _redirect_by_role($role)
    {
        switch ($role) {
            case 'admin':
                redirect('admin/dashboard');
                break;
            case 'staff':
                redirect('staff/dashboard');
                break;
            case 'doctor':
                redirect('doctor/dashboard');
                break;
            case 'user':
            default:
                redirect('/');
                break;
        }
    }

    // Check if user is already logged in
    private function _check_logged_in()
    {
        $role = $this->session->userdata('role');
        if ($this->session->userdata('is_logged_in')) {
            $this->_redirect_by_role($role);
        }
    }

    // Show login form
    public function login()
    {
        $this->_check_logged_in();
        $this->call->view('auth/login');
    }

    // Show registration form
    public function register()
    {
        $this->_check_logged_in();
        $this->call->view('auth/register');
    }

    // Handle user login
    public function login_submit()
    {
        $this->_check_logged_in();
        $data = $this->io->post();

        $identifier = $data['username'] ?? '';

        $this->form_validation
            ->name('username|Username or Email')->required()
            ->name('password|Password')->required();

        if ($this->form_validation->run()) {

            $user = null;

            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $user = $this->UserModel->filter(['email' => $identifier])->get();
            }

            if (!$user) {
                $user = $this->UserModel->find_by_username($identifier);
            }

            if ($user && password_verify($data['password'], $user['password'])) {

                // Check if account is suspended
                if (isset($user['is_suspended']) && $user['is_suspended'] == 1) {
                    $reason = $user['suspension_reason'] ?? 'Your account has been suspended. Please contact support.';
                    $data['error'] = $reason;
                    $data['username'] = $identifier;
                    $this->call->view('auth/login', $data);
                    return;
                }

                $session_data = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'is_logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);

                $this->_redirect_by_role($user['role']);
            } else {
                $data['error'] = 'Invalid username/email or password.';
                $data['username'] = $identifier;
                $this->call->view('auth/login', $data);
            }
        } else {
            $data['error'] = $this->form_validation->errors();
            $data['username'] = $identifier;
            $this->call->view('auth/login', $data);
        }
    }

    // Handle user registration
    public function register_submit()
    {
        $this->_check_logged_in();
        $data = $this->io->post();

        $this->form_validation
            ->name('full_name|Full Name')->required()->valid_name()
            ->name('email|Email')->required()->valid_email()->is_unique('users', 'email', $data['email'])
            ->name('username|Username')->required()->alpha_numeric()->is_unique('users', 'username', $data['username'])
            ->name('password|Password')->required()->min_length(6)
            ->name('confirm_password|Confirm Password')->required()->matches('password');

        $this->form_validation->name('role|Role')->required()->in_list('user,staff,admin,doctor');

        if ($this->form_validation->run()) {

            $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);

            $final_role = $this->io->post('role');

            // If registering as doctor, create doctor profile
            if ($final_role === 'doctor') {
                $this->call->model('DoctorModel');
            }

            $new_user_data = [
                'full_name' => $this->io->post('full_name'),
                'email'     => $this->io->post('email'),
                'username' => $data['username'],
                'password' => $hashed_password,
                'role' => $final_role
            ];

            $user_id = $this->UserModel->create_user($new_user_data);
            
            if ($user_id) {
                // If registering as doctor, create doctor profile and link to user
                if ($final_role === 'doctor') {
                    $doctor_data = [
                        'user_id' => $user_id,
                        'name' => $this->io->post('full_name'),
                        'specialty' => $this->io->post('specialty') ?: 'General Dentistry',
                        'email' => $this->io->post('email')
                    ];
                    $this->DoctorModel->createDoctor($doctor_data);
                }
                
                $this->session->set_flashdata('success_message', 'Registration successful! You may now login.');
                redirect('login');
            } else {
                $this->session->set_flashdata('error_message', 'A database error occurred during registration.');
                redirect('register');
            }
        } else {
            $data['errors'] = $this->form_validation->get_errors();
            $data['username'] = $this->io->post('username');
            $data['full_name'] = $this->io->post('full_name');
            $data['email'] = $this->io->post('email');

            $this->call->view('auth/register', $data);
        }
    }

    // Logout user
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }

    // View user profile
    public function profile()
    {
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
        }

        $this->call->model(['AppointmentModel', 'DoctorModel', 'ServiceModel']);

        $user_id = $this->session->userdata('user_id');
        $data['user'] = $this->UserModel->find($user_id);

        $data['appointments'] = $this->AppointmentModel->filter(['user_id' => $user_id])->get_all();

        $data['doctors'] = array_column($this->DoctorModel->all() ?? [], null, 'id');
        $data['services'] = array_column($this->ServiceModel->all() ?? [], null, 'id');

        $this->call->view('auth/profile', $data);
    }

    // Edit user profile
    public function profile_edit_submit()
    {
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
        }

        $user_id = $this->session->userdata('user_id');
        $data = $this->io->post();

        $this->form_validation
            ->name('full_name|Full Name')->required()->valid_name()
            ->name('email|Email')->required()->valid_email();

        if ($this->form_validation->run()) {

            $update_data = [
                'full_name' => $this->io->post('full_name'),
                'email' => $this->io->post('email')
            ];

            if (!empty($this->io->post('new_password'))) {
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

    // Delete user profile
    public function profile_delete()
    {
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
        }

        $user_id = $this->session->userdata('user_id');
        $this->UserModel->delete($user_id);

        $this->session->sess_destroy();
        $this->session->set_flashdata('success_message', 'Your account has been deleted.');

        redirect('/');
    }

    // Cancel a user's appointment
    public function profile_cancel_appointment()
    {
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
        }

        $appointment_id = $this->io->post('appointment_id');
        if (empty($appointment_id)) {
            $this->session->set_flashdata('error_message', 'Invalid appointment.');
            redirect('profile');
        }

        $this->call->model('AppointmentModel');
        $user_id = $this->session->userdata('user_id');

        $success = $this->AppointmentModel->cancel($appointment_id, $user_id);

        if ($success) {
            $this->session->set_flashdata('success_message', 'Appointment cancelled successfully.');
        } else {
            $this->session->set_flashdata('error_message', 'Failed to cancel the appointment.');
        }

        redirect('profile');
    }

    // Clear all appointment history (except confirmed)
    public function clear_history()
    {
        if (!$this->session->userdata('is_logged_in')) {
            redirect('login');
        }

        $this->call->model('AppointmentModel');
        $user_id = $this->session->userdata('user_id');

        $success = $this->AppointmentModel->clear_history($user_id);

        if ($success) {
            $this->session->set_flashdata('success_message', 'Appointment history cleared successfully.');
        } else {
            $this->session->set_flashdata('error_message', 'Failed to clear appointment history.');
        }

        redirect('profile');
    }

    // Initiate Google OAuth login
    public function google_login()
    {
        $client_id = '298110887489-apjnbc92tgt4k0d8t107fg1v7kntin44.apps.googleusercontent.com';
        // $redirect_uri = 'https://dentalcare-health.ct.ws/auth/google_callback';
         $redirect_uri = 'http://localhost:3000/auth/google_callback';
        // $redirect_uri = 'https://dentalcare-health.onrender.com/auth/google_callback';
        
        $params = [
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online'
        ];
        
        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    // Callback function to handle Google OAuth response
    public function google_callback()
    {
        $code = $this->io->get('code');

        if ($code) {
            $client_id = '298110887489-apjnbc92tgt4k0d8t107fg1v7kntin44.apps.googleusercontent.com';
            $client_secret = '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';
            // $redirect_uri = 'https://dentalcare-health.ct.ws/auth/google_callback';
            $redirect_uri = 'http://localhost:3000/auth/google_callback';
            // $redirect_uri = 'https://dentalcare-health.onrender.com/auth/google_callback';

            // Exchange authorization code for access token
            $token_url = 'https://oauth2.googleapis.com/token';
            $token_data = [
                'code' => $code,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code'
            ];

            $ch = curl_init($token_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $token = json_decode($response, true);

            if (isset($token['error']) || !isset($token['access_token'])) {
                redirect('/auth/login?error=google_failed');
                return;
            }

            // Get user info using access token
            $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
            $ch = curl_init($userinfo_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token['access_token']]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $userinfo_response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($userinfo_response, true);

            if (!isset($data['email'])) {
                redirect('/auth/login?error=google_failed');
                return;
            }

            $user_email = $data['email'];
            $user_name = $data['name'] ?? $data['email'];

            $this->call->model('UserModel');
            $existing_user = $this->UserModel->findUserByEmail($user_email);

            if ($existing_user) {
                $session_data = [
                    'user_id' => $existing_user['id'],
                    'username' => $existing_user['username'] ?? $existing_user['name'] ?? $existing_user['email'],
                    'user_email' => $existing_user['email'],
                    'role' => $existing_user['role'],
                    'is_logged_in' => TRUE,
                ];
                $this->session->set_userdata($session_data);

                $this->_redirect_by_role($existing_user['role']);
            } else {
                $new_user_data = [
                    'name' => $user_name,
                    'email' => $user_email,
                    'password' => '',
                    'role' => 'user',
                    'email_verified_at' => date('Y-m-d H:i:s')
                ];

                if ($this->UserModel->register($new_user_data)) {
                    $new_user = $this->UserModel->findUserByEmail($user_email);
                    if ($new_user) {
                        $session_data = [
                            'user_id' => $new_user['id'],
                            'username' => $new_user['username'] ?? $new_user['name'] ?? $new_user['email'],
                            'user_email' => $new_user['email'],
                            'role' => $new_user['role'],
                            'is_logged_in' => TRUE,
                        ];
                        $this->session->set_userdata($session_data);
                    }
                    $this->_redirect_by_role($new_user['role']);
                } else {
                    redirect('/auth/register?error=google_reg_failed');
                }
            }
        } else {
            redirect('/auth/login');
        }
    }
}