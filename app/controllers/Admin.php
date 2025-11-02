<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Admin extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UserModel');
        $this->call->library('Form_validation');
        $this->call->helper('url');
        $this->call->helper('language');

        // Ensure only admin can access admin routes
        if ($this->session->userdata('role') !== 'admin') {
            if ($this->router->get_method() !== 'dashboard') {
                $this->session->set_flashdata('error_message', 'Admin privileges required.');
                redirect('login');
            }
        }
    }

    // Admin dashboard
    public function dashboard()
    {
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error_message', 'Admin privileges required.');
            redirect('login');
        }

        $LAVA = lava_instance();

        $admin_user_id = $this->session->userdata('user_id');
        if (!$admin_user_id) {
            $this->session->set_flashdata('error_message', 'Session invalid. Please login again.');
            redirect('login');
        }

        $this->call->model('AppointmentModel');

        if (!isset($LAVA->db)) {
            $this->call->database();
        }

        $admin_details = $this->UserModel->find($admin_user_id);
        if (!$admin_details) {
            $this->session->set_flashdata('error_message', 'Admin account not found. Please contact support.');
            $this->session->sess_destroy();
            redirect('login');
        }

        // Fetch statistics
        $total_users = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_staff = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'staff'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_admin = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_appointments = $LAVA->db->raw("SELECT COUNT(*) AS count FROM appointments")->fetch(PDO::FETCH_ASSOC)['count'];
        $all_users = $this->UserModel->all();

        // Prepare initial calendar data for the current month (server-side fallback)
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
            'total_users' => $total_users,
            'total_staff' => $total_staff,
            'total_admin' => $total_admin,
            'total_appointments' => $total_appointments,
            'all_users' => $all_users ?? [],
            'userDetails' => $admin_details,
            'errors' => $this->session->flashdata('errors') ?? [],
            'post_data' => $this->session->flashdata('post_data') ?? [],
            'initial_calendar' => $initial_calendar,
        ];

        $this->call->view('admin/dashboard', $data);
    }

    // Add or update staff/admin user
    public function admin_staff_add_update($id = null)
    {
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error_message', 'Admin privileges required.');
            redirect('login');
        }

        if ($this->io->method() !== 'POST') {
            redirect('admin/dashboard');
        }

        $post_data = $this->io->post();
        $is_update = !empty($id);

        $this->form_validation
            ->name('full_name|Full Name')->required()->valid_name()
            ->name('email|Email')->required()->valid_email()
            ->name('role|Role')->required()->in_list('admin,staff');

        if (!$is_update) {
            $this->form_validation
                ->name('username|Username')->required()->alpha_numeric()->is_unique('users', 'username', $post_data['username'])
                ->name('password|Password')->required()->min_length(6);
        }

        if ($this->form_validation->run()) {
            $save_data = $post_data;
            unset($save_data['lava_csrf_token']);
            unset($save_data[config_item('csrf_token_name')]);
            unset($save_data['id']);

            if ($is_update) {
                $update_id = $id;

                if (!empty($post_data['password'])) {
                    $save_data['password'] = password_hash($post_data['password'], PASSWORD_BCRYPT);
                } else {
                    unset($save_data['password']);
                }

                unset($save_data['username']);

                $this->UserModel->update($update_id, $save_data);
                $this->session->set_flashdata('success_message', "User #{$update_id} updated successfully.");
            } else {
                $save_data['password'] = password_hash($post_data['password'], PASSWORD_BCRYPT);
                $this->UserModel->insert($save_data);
                $this->session->set_flashdata('success_message', "New {$save_data['role']} account created successfully.");
            }
        } else {
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            $this->session->set_flashdata('post_data', $post_data);
            $this->session->set_flashdata('error_message', 'Validation failed. Please check the form.');
        }

        redirect('admin/dashboard');
    }

    // Delete staff/admin user
    public function admin_staff_delete($id)
    {
        if ($this->session->userdata('role') !== 'admin') {
            $this->session->set_flashdata('error_message', 'Admin privileges required.');
            redirect('login');
        }

        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid user ID.');
            redirect('admin/dashboard');
        }

        $user = $this->UserModel->find($id);

        if (!$user) {
            $this->session->set_flashdata('error_message', 'User not found.');
            redirect('admin/dashboard');
        }

        if ($user['id'] == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error_message', 'Cannot delete your own active Admin account.');
            redirect('admin/dashboard');
        }

        $this->UserModel->delete($id);
        $this->session->set_flashdata('success_message', "User '{$user['username']}' ({$user['role']}) deleted successfully.");
        redirect('admin/dashboard');
    }
}