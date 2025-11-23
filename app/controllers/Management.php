<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Management extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model(['DoctorModel', 'ServiceModel', 'AppointmentModel', 'UserModel']);
        $this->call->library('Form_validation');
        $this->call->helper(['url', 'language']);
        $this->_check_admin_or_staff();
    }

    // Ensure user is admin or staff
    private function _check_admin_or_staff()
    {
        $role = $this->session->userdata('role');
        if (!$this->session->userdata('is_logged_in') || !in_array($role, ['admin', 'staff'])) {
            $this->session->set_flashdata('error_message', 'Access denied. Admin or Staff privileges required.');
            redirect('login');
        }
    }

    // Ensure user is admin
    private function _check_admin()
    {
        $role = $this->session->userdata('role');
        if ($role !== 'admin') {
            $this->session->set_flashdata('error_message', 'Access denied. Admin privileges required.');
            redirect('management/appointments');
        }
    }

    // Fetch logged-in user details
    private function _fetchUserDetails()
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
        return $userDetails;
    }

    // View all appointments
    public function appointments()
    {
        $userDetails = $this->_fetchUserDetails();
        $data['appointments'] = $this->AppointmentModel->all() ?? [];
        $data['doctors'] = array_column($this->DoctorModel->all() ?? [], null, 'id');
        $data['services'] = array_column($this->ServiceModel->all() ?? [], null, 'id');
        $data['users'] = array_column($this->UserModel->all() ?? [], null, 'id');
        $data['userDetails'] = $userDetails;
        $this->call->view('admin/appointments', $data);
    }

    // View and manage doctors
    public function doctors()
    {
        $userDetails = $this->_fetchUserDetails();
        
        // Get all doctors with their linked user accounts
        $doctors_list = $this->DoctorModel->getAllWithUsers();
        
        $data['doctors_list_json'] = json_encode(array_column($doctors_list, null, 'id'));
        $data['doctors'] = $doctors_list;
        $data['errors'] = $this->session->flashdata('errors');
        $data['post_data'] = $this->session->flashdata('post_data');
        $data['userDetails'] = $userDetails;
        $this->call->view('admin/doctor_management', $data);
    }

    // View and manage services
    public function services()
    {
        $userDetails = $this->_fetchUserDetails();
        $services_list = $this->ServiceModel->all() ?? [];
        $data['services_list_json'] = json_encode(array_column($services_list, null, 'id'));
        $data['services'] = $services_list;
        $data['errors'] = $this->session->flashdata('errors');
        $data['post_data'] = $this->session->flashdata('post_data');
        $data['userDetails'] = $userDetails;
        $this->call->view('admin/service_management', $data);
    }

    // JSON: Appointments for a month (admin/staff only)
    public function appointments_json()
    {
        // Access control already enforced in constructor (_check_admin_or_staff)
        $month = (int) ($this->io->get('month') ?? date('n'));
        $year  = (int) ($this->io->get('year')  ?? date('Y'));
        if ($month < 1 || $month > 12) { $month = (int) date('n'); }
        if ($year < 1970 || $year > 2100) { $year = (int) date('Y'); }

        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

        // Ensure DB loaded for raw queries
        if (!isset($this->db)) {
            $this->call->database();
        }

        // Join to enrich with names
        $sql = "SELECT a.id, a.user_id, a.doctor_id, a.service_id, a.appointment_date, a.time_slot, a.status,
                       u.username, COALESCE(u.full_name, u.username) AS user_full_name,
                       d.name AS doctor_name, d.specialty AS doctor_specialty,
                       s.name AS service_name, s.duration_mins, s.price
                FROM appointments a
                LEFT JOIN users u ON u.id = a.user_id
                LEFT JOIN doctors d ON d.id = a.doctor_id
                LEFT JOIN services s ON s.id = a.service_id
                WHERE a.appointment_date BETWEEN ? AND ?
                ORDER BY a.appointment_date ASC, a.time_slot ASC";

        $stmt = $this->db->raw($sql, [$start, $end]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Map to client-friendly events
        $events = array_map(function($r) {
            return [
                'id' => (int)$r['id'],
                'date' => $r['appointment_date'],
                'time' => $r['time_slot'],
                'status' => $r['status'],
                'user' => $r['user_full_name'] ?? $r['username'] ?? 'User',
                'doctor' => $r['doctor_name'] ?? 'Doctor',
                'specialty' => $r['doctor_specialty'] ?? null,
                'service' => $r['service_name'] ?? 'Service',
                'duration_mins' => isset($r['duration_mins']) ? (int)$r['duration_mins'] : null,
                'price' => isset($r['price']) ? (float)$r['price'] : null,
            ];
        }, $rows);

        $this->io->send_json([
            'month' => $month,
            'year'  => $year,
            'start' => $start,
            'end'   => $end,
            'events'=> $events,
        ]);
    }

    // Confirm an appointment
    public function appointment_confirm($id)
    {
        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid appointment ID.');
            redirect('management/appointments');
        }
        $this->AppointmentModel->update($id, ['status' => 'confirmed']);
        $this->session->set_flashdata('success_message', "Appointment #{$id} confirmed successfully.");
        redirect('management/appointments');
    }

    // Cancel an appointment
    public function appointment_cancel($id)
    {
        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid appointment ID.');
            redirect('management/appointments');
        }

        // Fetch appointment details
        $appointment = $this->AppointmentModel->find($id);
        if (!$appointment) {
            $this->session->set_flashdata('error_message', 'Appointment not found.');
            redirect('management/appointments');
        }

        // Prepare update data
        $update = ['status' => 'cancelled'];

        // If payment was made at clinic and marked as paid, reset payment status
        if ($appointment['payment_method'] === 'clinic' && $appointment['payment_status'] === 'paid') {
            $update['payment_status'] = 'unpaid';
            $update['paid_at'] = null;
        }

        $this->AppointmentModel->update($id, $update);
        $this->session->set_flashdata('success_message', "Appointment #{$id} cancelled successfully.");
        redirect('management/appointments');
    }

    // Decline an appointment with a message (accessible by admin and staff)
    public function appointment_decline()
    {
        if ($this->io->method() !== 'POST') {
            redirect('management/appointments');
        }

        $post = $this->io->post();
        $appointment_id = $post['appointment_id'] ?? null;
        $message = trim($post['decline_message'] ?? '');

        if (empty($appointment_id) || $message === '') {
            $this->session->set_flashdata('error_message', 'Appointment ID and decline message are required.');
            redirect('management/appointments');
        }

        // Ensure appointment exists
        $appointment = $this->AppointmentModel->find($appointment_id);
        if (!$appointment) {
            $this->session->set_flashdata('error_message', 'Appointment not found.');
            redirect('management/appointments');
        }

        // Update appointment status and store decline message
        $update = [
            'status' => 'declined',
            'decline_message' => $message
        ];

        // If payment was made at clinic and marked as paid, reset payment status
        if ($appointment['payment_method'] === 'clinic' && $appointment['payment_status'] === 'paid') {
            $update['payment_status'] = 'unpaid';
            $update['paid_at'] = null;
        }

        $this->AppointmentModel->update($appointment_id, $update);

        $this->session->set_flashdata('success_message', 'Appointment has been declined.');
        redirect('management/appointments');
    }

    public function appointment_mark_paid($id)
    {
        // Ensure appointment exists
        $appointment = $this->AppointmentModel->find($id);
        if (!$appointment) {
            $this->session->set_flashdata('error_message', 'Appointment not found.');
            redirect('management/appointments');
        }

        // Check appointment status - cannot mark cancelled or declined appointments as paid
        if (in_array($appointment['status'], ['cancelled', 'declined'])) {
            $this->session->set_flashdata('error_message', 'Cannot mark cancelled or declined appointments as paid.');
            redirect('management/appointments');
        }

        // Check if payment method is clinic
        if ($appointment['payment_method'] !== 'clinic') {
            $this->session->set_flashdata('error_message', 'Only clinic payment appointments can be marked as paid manually.');
            redirect('management/appointments');
        }

        // Check if already paid
        if ($appointment['payment_status'] === 'paid') {
            $this->session->set_flashdata('error_message', 'This appointment is already marked as paid.');
            redirect('management/appointments');
        }

        // Update payment status to paid
        $update = [
            'payment_status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s')
        ];

        $this->AppointmentModel->update($id, $update);

        $this->session->set_flashdata('success_message', 'Appointment payment has been marked as paid.');
        redirect('management/appointments');
    }

    // Add or update doctor
    public function doctor_add_update($id = null)
    {
        if ($this->io->method() !== 'POST') {
            redirect('management/doctors');
        }

        $post_data = $this->io->post();
        $is_update = !empty($id);

        // Basic validation
        $this->form_validation
            ->name('name|Doctor Name')->required()->valid_name()
            ->name('specialty|Specialty')->required()
            ->name('email|Email')->required()->valid_email();

        // Username validation for new doctors or if username is provided
        if (!$is_update || !empty($post_data['username'])) {
            $this->form_validation->name('username|Username')->required()->min_length(3)->max_length(50);
        }

        // Password validation - required for new doctors or if password is provided
        if (!$is_update) {
            $this->form_validation->name('password|Password')->required()->min_length(6);
        } else if (!empty($post_data['password'])) {
            $this->form_validation->name('password|Password')->min_length(6);
        }

        // Check for duplicate email in doctors table
        $existingDoctor = null;
        if ($is_update) {
            $existingDoctor = $this->DoctorModel->filter(['email' => $post_data['email']])->not_where('id', $id)->get();
        } else {
            $existingDoctor = $this->DoctorModel->filter(['email' => $post_data['email']])->get();
        }

        if ($existingDoctor) {
            $this->form_validation->set_error('email', 'Email address is already in use by another doctor.');
        }

        // Check for duplicate username in users table
        if (!empty($post_data['username'])) {
            if ($is_update) {
                $doctor = $this->DoctorModel->find($id);
                $existingUser = $this->UserModel->filter(['username' => $post_data['username']])
                    ->not_where('id', $doctor['user_id'] ?? 0)
                    ->get();
            } else {
                $existingUser = $this->UserModel->find_by_username($post_data['username']);
            }

            if ($existingUser) {
                $this->form_validation->set_error('username', 'Username is already taken.');
            }
        }

        if ($this->form_validation->run()) {
            if ($is_update) {
                // UPDATE mode
                $doctor = $this->DoctorModel->find($id);
                if (!$doctor) {
                    $this->session->set_flashdata('error_message', 'Doctor not found.');
                    redirect('management/doctors');
                }

                // Update doctor table
                $doctor_data = [
                    'name' => $post_data['name'],
                    'specialty' => $post_data['specialty'],
                    'email' => $post_data['email']
                ];
                $this->DoctorModel->update($id, $doctor_data);

                // Handle user account
                if (!empty($doctor['user_id'])) {
                    // Update existing user account
                    $user_data = [
                        'full_name' => $post_data['name'],
                        'email' => $post_data['email']
                    ];
                    
                    if (!empty($post_data['username'])) {
                        $user_data['username'] = $post_data['username'];
                    }
                    
                    if (!empty($post_data['password'])) {
                        $user_data['password'] = password_hash($post_data['password'], PASSWORD_DEFAULT);
                    }
                    
                    $this->UserModel->update($doctor['user_id'], $user_data);
                } else if (!empty($post_data['username']) && !empty($post_data['password'])) {
                    // Create new user account for existing doctor
                    $user_data = [
                        'username' => $post_data['username'],
                        'password' => password_hash($post_data['password'], PASSWORD_DEFAULT),
                        'full_name' => $post_data['name'],
                        'email' => $post_data['email'],
                        'role' => 'doctor'
                    ];
                    $user_id = $this->UserModel->create_user($user_data);
                    
                    if ($user_id) {
                        $this->DoctorModel->update($id, ['user_id' => $user_id]);
                    }
                }

                $this->session->set_flashdata('success_message', 'Doctor updated successfully.');
            } else {
                // INSERT mode - always create user account
                $user_data = [
                    'username' => $post_data['username'],
                    'password' => password_hash($post_data['password'], PASSWORD_DEFAULT),
                    'full_name' => $post_data['name'],
                    'email' => $post_data['email'],
                    'role' => 'doctor'
                ];
                
                $user_id = $this->UserModel->create_user($user_data);
                
                if ($user_id) {
                    $doctor_data = [
                        'name' => $post_data['name'],
                        'specialty' => $post_data['specialty'],
                        'email' => $post_data['email'],
                        'user_id' => $user_id
                    ];
                    
                    $this->DoctorModel->insert($doctor_data);
                    $this->session->set_flashdata('success_message', 'New doctor added successfully with login account.');
                } else {
                    $this->session->set_flashdata('error_message', 'Failed to create user account.');
                }
            }
        } else {
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            $this->session->set_flashdata('post_data', $post_data);
            $this->session->set_flashdata('error_message', 'Validation failed. Please check the form.');
        }
        redirect('management/doctors');
    }

    // Delete a doctor
    public function doctor_delete($id)
    {
        $this->_check_admin();
        $doctor = $this->DoctorModel->find($id);
        if (!$doctor) {
            $this->session->set_flashdata('error_message', 'Doctor not found.');
            redirect('management/doctors');
        }

        // Delete associated appointments first
        $this->AppointmentModel->filter(['doctor_id' => $id])->delete();
        
        // Delete user account if linked
        if (!empty($doctor['user_id'])) {
            $this->UserModel->delete($doctor['user_id']);
        }
        
        // Delete doctor record
        $this->DoctorModel->delete($id);

        $this->session->set_flashdata('success_message', "Doctor '{$doctor['name']}' deleted successfully.");
        redirect('management/doctors');
    }

    // Add or update service
    public function service_add_update($id = null)
    {
        if ($this->io->method() !== 'POST') {
            redirect('management/services');
        }

        $post_data = $this->io->post();
        $is_update = !empty($id);

        $this->form_validation
            ->name('name|Service Name')->required()
            ->name('price|Price')->required()->numeric()
            ->name('duration_mins|Duration')->required()->numeric();

        if ($this->form_validation->run()) {
            $save_data = [
                'name' => $post_data['name'],
                'price' => $post_data['price'],
                'duration_mins' => $post_data['duration_mins']
            ];

            if ($is_update) {
                $this->ServiceModel->update($id, $save_data);
                $this->session->set_flashdata('success_message', 'Service updated successfully.');
            } else {
                $this->ServiceModel->insert($save_data);
                $this->session->set_flashdata('success_message', 'New service added successfully.');
            }
        } else {
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            $this->session->set_flashdata('post_data', $post_data);
            $this->session->set_flashdata('error_message', 'Validation failed. Please check the form.');
        }
        redirect('management/services');
    }

    // Delete a service
    public function service_delete($id)
    {
        $this->_check_admin();
        $service = $this->ServiceModel->find($id);
        if (!$service) {
            $this->session->set_flashdata('error_message', 'Service not found.');
            redirect('management/services');
        }

        $this->AppointmentModel->filter(['service_id' => $id])->delete();
        $this->ServiceModel->delete($id);

        $this->session->set_flashdata('success_message', "Service '{$service['name']}' deleted successfully.");
        redirect('management/services');
    }

    // Suspend a user account (Admin/Staff can access)
    public function user_suspend($id)
    {
        // Admin or Staff can suspend users
        $this->_check_admin_or_staff();
        
        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid user ID.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/dashboard');
        }

        $user = $this->UserModel->find($id);
        if (!$user) {
            $this->session->set_flashdata('error_message', 'User not found.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/dashboard');
        }

        // Prevent suspending admin accounts
        if ($user['role'] === 'admin') {
            $this->session->set_flashdata('error_message', 'Cannot suspend admin accounts.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/dashboard');
        }

        // Prevent suspending self
        if ($user['id'] == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error_message', 'Cannot suspend your own account.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/dashboard');
        }

        // Get optional reason from query parameter
        $reason = $this->io->get('reason');
        $default_reason = 'Account suspended by ' . $this->session->userdata('username');
        
        $update_data = [
            'is_suspended' => 1,
            'suspended_at' => date('Y-m-d H:i:s'),
            'suspension_reason' => !empty($reason) ? trim($reason) : $default_reason
        ];

        $this->UserModel->update($id, $update_data);
        $this->session->set_flashdata('success_message', "User '{$user['username']}' has been suspended.");
        redirect($_SERVER['HTTP_REFERER'] ?? 'admin/dashboard');
    }

    // Unsuspend a user account (Admin/Staff can access)
    public function user_unsuspend($id)
    {
        // Admin or Staff can unsuspend users
        $this->_check_admin_or_staff();
        
        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid user ID.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/dashboard');
        }

        $user = $this->UserModel->find($id);
        if (!$user) {
            $this->session->set_flashdata('error_message', 'User not found.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/dashboard');
        }

        $update_data = [
            'is_suspended' => 0,
            'suspended_at' => null,
            'suspension_reason' => null
        ];

        $this->UserModel->update($id, $update_data);
        $this->session->set_flashdata('success_message', "User '{$user['username']}' has been unsuspended.");
        redirect($_SERVER['HTTP_REFERER'] ?? 'admin/dashboard');
    }

    // Toggle doctor availability (Admin/Staff can access)
    public function doctor_toggle_availability($id)
    {
        // Admin or Staff can toggle doctor availability
        $this->_check_admin_or_staff();
        
        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid doctor ID.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'management/doctors');
        }

        // Use raw SQL to avoid the filter binding issue
        if (!isset($this->db)) {
            $this->call->database();
        }

        $sql = "SELECT * FROM doctors WHERE id = ? LIMIT 1";
        $stmt = $this->db->raw($sql, [$id]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doctor) {
            $this->session->set_flashdata('error_message', 'Doctor not found.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'management/doctors');
        }

        $result = $this->DoctorModel->toggleAvailability($id);
        if ($result) {
            $new_status = $doctor['is_available'] ? 'unavailable' : 'available';
            $this->session->set_flashdata('success_message', "Dr. {$doctor['name']} is now {$new_status}.");
        } else {
            $this->session->set_flashdata('error_message', 'Failed to update doctor availability.');
        }
        
        redirect($_SERVER['HTTP_REFERER'] ?? 'management/doctors');
    }
}