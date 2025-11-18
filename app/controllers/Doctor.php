<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Doctor extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model(['DoctorModel', 'AppointmentModel', 'ServiceModel', 'UserModel']);
        $this->call->library('Form_validation');
        $this->call->helper(['url', 'language']);
        $this->_check_doctor();
    }

    // Ensure user is doctor
    private function _check_doctor()
    {
        $role = $this->session->userdata('role');
        if (!$this->session->userdata('is_logged_in') || $role !== 'doctor') {
            $this->session->set_flashdata('error_message', 'Access denied. Doctor privileges required.');
            redirect('login');
        }
    }

    // Fetch logged-in doctor details
    private function _fetchDoctorDetails()
    {
        $loggedInUserId = $this->session->userdata('user_id');
        if (!$loggedInUserId) {
            $this->session->set_flashdata('error_message', 'Session invalid. Please login again.');
            redirect('login');
        }
        
        $userDetails = $this->UserModel->find($loggedInUserId);
        if (!$userDetails) {
            $this->session->set_flashdata('error_message', 'User account not found.');
            $this->session->sess_destroy();
            redirect('login');
        }

        // Fetch doctor profile linked to this user
        $doctorProfile = $this->DoctorModel->filter(['user_id' => $loggedInUserId])->get();
        
        return [
            'user' => $userDetails,
            'doctor' => $doctorProfile
        ];
    }

    // Doctor Dashboard
    public function dashboard()
    {
        $details = $this->_fetchDoctorDetails();
        $data['userDetails'] = $details['user'];
        $data['doctorProfile'] = $details['doctor'];

        // Get doctor's appointments
        if ($details['doctor']) {
            $doctorId = $details['doctor']['id'];
            
            // Ensure DB loaded for raw queries
            if (!isset($this->db)) {
                $this->call->database();
            }

            // Fetch all appointments for this doctor
            $sql = "SELECT a.*, 
                           u.username, u.full_name as user_full_name, u.email as user_email,
                           s.name as service_name, s.price, s.duration_mins
                    FROM appointments a
                    LEFT JOIN users u ON u.id = a.user_id
                    LEFT JOIN services s ON s.id = a.service_id
                    WHERE a.doctor_id = ?
                    ORDER BY a.appointment_date DESC, a.time_slot DESC";
            
            $stmt = $this->db->raw($sql, [$doctorId]);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            $data['appointments'] = $appointments;
            
            // Separate appointments by status
            $data['pending_appointments'] = array_filter($appointments, function($apt) {
                return $apt['status'] === 'pending';
            });
            
            $data['confirmed_appointments'] = array_filter($appointments, function($apt) {
                return $apt['status'] === 'confirmed';
            });
        } else {
            $data['appointments'] = [];
            $data['pending_appointments'] = [];
            $data['confirmed_appointments'] = [];
        }

        $this->call->view('doctor/dashboard', $data);
    }

    // View and edit doctor profile
    public function profile()
    {
        $details = $this->_fetchDoctorDetails();
        $data['userDetails'] = $details['user'];
        $data['doctorProfile'] = $details['doctor'];
        
        $data['errors'] = $this->session->flashdata('errors');
        
        $this->call->view('doctor/profile', $data);
    }

    // Update doctor profile
    public function profile_update()
    {
        $details = $this->_fetchDoctorDetails();
        $userId = $details['user']['id'];
        $doctorProfile = $details['doctor'];

        // Basic validation
        $this->form_validation
            ->name('full_name|Full Name')->required()->valid_name()
            ->name('email|Email')->required()->valid_email()
            ->name('specialty|Specialty')->required();

        // Password validation only if new password provided
        if (!empty($this->io->post('new_password'))) {
            $this->form_validation
                ->name('new_password|New Password')->required()->min_length(6)
                ->name('confirm_new_password|Confirm Password')->required()->matches('new_password');
        }

        if ($this->form_validation->run()) {
            // Update user table
            $userData = [
                'full_name' => $this->io->post('full_name'),
                'email' => $this->io->post('email')
            ];

            // Add password to update if provided
            if (!empty($this->io->post('new_password'))) {
                $userData['password'] = password_hash($this->io->post('new_password'), PASSWORD_BCRYPT);
            }

            $this->UserModel->update($userId, $userData);

            // Update doctor profile if exists
            if ($doctorProfile) {
                $doctorData = [
                    'name' => $this->io->post('full_name'),
                    'specialty' => $this->io->post('specialty'),
                    'email' => $this->io->post('email')
                ];
                $this->DoctorModel->update($doctorProfile['id'], $doctorData);
            }

            $this->session->set_flashdata('success_message', 'Profile updated successfully.');
            redirect('doctor/profile');
        } else {
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            redirect('doctor/profile');
        }
    }

    // Delete doctor account (POST method for security)
    public function profile_delete()
    {
        // Verify POST method
        if ($this->io->server('REQUEST_METHOD') !== 'POST') {
            $this->session->set_flashdata('error_message', 'Invalid request method.');
            redirect('doctor/profile');
            return;
        }

        $details = $this->_fetchDoctorDetails();
        $userId = $details['user']['id'];
        $doctorProfile = $details['doctor'];

        // Note: Deleting user will set doctor.user_id to NULL due to ON DELETE SET NULL
        // The doctor profile will remain in the system for admin management
        if ($doctorProfile) {
            // Unlink user from doctor profile
            $this->DoctorModel->update($doctorProfile['id'], ['user_id' => NULL]);
        }

        // Delete user account
        $this->UserModel->delete($userId);

        // Logout
        $this->session->sess_destroy();
        $this->session->set_flashdata('success_message', 'Your account has been deleted successfully.');
        redirect('login');
    }
}
