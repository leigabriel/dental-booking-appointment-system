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

        $role = $this->session->userdata('role');
        if (!$this->session->userdata('is_logged_in') || !in_array($role, ['staff', 'admin'])) {
            $this->session->set_flashdata('error_message', 'Access denied. Staff privileges required.');
            redirect('login');
        }
    }

    public function dashboard()
    {
        $LAVA = lava_instance();

        $staff_user_id = $this->session->userdata('user_id');
        $staff_details = $this->UserModel->find($staff_user_id);
        $patient_users = $this->UserModel->filter(['role' => 'user'])->get_all() ?? [];
        $total_patients = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_appointments = $LAVA->db->raw("SELECT COUNT(*) AS count FROM appointments")->fetch(PDO::FETCH_ASSOC)['count'];

        $data = [
            'all_users' => $patient_users,
            'total_patients' => $total_patients,
            'total_appointments' => $total_appointments,
            'staff_details' => $staff_details,
        ];

        $this->call->view('staff/dashboard', $data);
    }
}