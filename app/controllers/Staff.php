<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Staff extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model(['UserModel', 'AppointmentModel']);
        $this->call->database();
        $this->call->helper('url');
        $this->call->helper('language');

        // Ensure only staff can access staff routes
        $role = $this->session->userdata('role');
        if (!$this->session->userdata('is_logged_in') || !in_array($role, ['staff', 'admin'])) {
            $this->session->set_flashdata('error_message', 'Access denied. Staff privileges required.');
            redirect('login');
        }
    }

    // Staff dashboard
    public function dashboard()
    {
        $LAVA = lava_instance();

        $staff_user_id = $this->session->userdata('user_id');
        if (!$staff_user_id) {
            $this->session->set_flashdata('error_message', 'Session invalid. Please login again.');
            redirect('login');
        }

        $staff_details = $this->UserModel->find($staff_user_id);
        if (!$staff_details) {
            $this->session->set_flashdata('error_message', 'Staff account not found.');
            $this->session->sess_destroy();
            redirect('login');
        }

        // Fetch statistics
        $patient_users = $this->UserModel->filter(['role' => 'user'])->get_all() ?? [];
        $total_patients = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_appointments = $LAVA->db->raw("SELECT COUNT(*) AS count FROM appointments")->fetch(PDO::FETCH_ASSOC)['count'];

        $data = [
            'all_users' => $patient_users,
            'total_patients' => $total_patients,
            'total_appointments' => $total_appointments,
            'userDetails' => $staff_details,
        ];

        $this->call->view('staff/dashboard', $data);
    }
}