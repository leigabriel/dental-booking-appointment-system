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
        $doctors_list = $this->DoctorModel->all() ?? [];
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
        $this->AppointmentModel->update($id, ['status' => 'cancelled']);
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

        $this->form_validation
            ->name('name|Doctor Name')->required()->valid_name()
            ->name('specialty|Specialty')->required()
            ->name('email|Email')->required()->valid_email();

        $existingDoctor = null;
        if ($is_update) {
            $existingDoctor = $this->DoctorModel->filter(['email' => $post_data['email']])->not_where('id', $id)->get();
        } else {
            $existingDoctor = $this->DoctorModel->filter(['email' => $post_data['email']])->get();
        }

        if ($existingDoctor) {
            $this->form_validation->set_error('email', 'Email address is already in use by another doctor.');
        }

        if ($this->form_validation->run()) {
            $save_data = [
                'name' => $post_data['name'],
                'specialty' => $post_data['specialty'],
                'email' => $post_data['email']
            ];

            if ($is_update) {
                $this->DoctorModel->update($id, $save_data);
                $this->session->set_flashdata('success_message', 'Doctor updated successfully.');
            } else {
                $this->DoctorModel->insert($save_data);
                $this->session->set_flashdata('success_message', 'New doctor added successfully.');
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

        $this->AppointmentModel->filter(['doctor_id' => $id])->delete();
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
}