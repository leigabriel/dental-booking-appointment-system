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

        // Fetch statistics - Get ALL users (not just patients)
        $all_users = $LAVA->db->raw("SELECT id, username, full_name, email, role, is_suspended, suspended_at, suspension_reason FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $total_patients = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_doctors = $LAVA->db->raw("SELECT COUNT(*) AS count FROM doctors")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_appointments = $LAVA->db->raw("SELECT COUNT(*) AS count FROM appointments")->fetch(PDO::FETCH_ASSOC)['count'];

        // Prepare initial calendar data for the current month
        $month = (int) date('n');
        $year  = (int) date('Y');
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end   = date('Y-m-t', strtotime($start));

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
        $rows = $LAVA->db->raw($sql, [$start, $end])->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $initial_events = array_map(function($r){
            return [
                'id' => (int) $r['id'],
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

        $initial_calendar = [
            'month' => $month,
            'year'  => $year,
            'start' => $start,
            'end'   => $end,
            'events'=> $initial_events,
        ];

        $data = [
            'all_users' => $all_users,
            'total_patients' => $total_patients,
            'total_doctors' => $total_doctors,
            'total_appointments' => $total_appointments,
            'staff_details' => $staff_details,
            'initial_calendar' => $initial_calendar,
        ];

        $this->call->view('staff/dashboard', $data);
    }
}