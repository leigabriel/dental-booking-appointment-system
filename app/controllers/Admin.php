<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Admin extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UserModel');
        $this->call->helper('url');
        $this->call->helper('language');
    }

    public function dashboard()
    {
        $LAVA = lava_instance();

        // Ensure database connection is active 
        if (!isset($LAVA->db)) {
            $this->call->database();
        }

        // 1. Fetch counts (using fetch() to correct the previous PDOStatement error)
        $total_users = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_staff = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'staff'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_admin = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetch(PDO::FETCH_ASSOC)['count'];

        // 2. Fetch ALL user records for the table display
        $all_users = $this->UserModel->all();

        $data = [
            'total_users' => $total_users,
            'total_staff' => $total_staff,
            'total_admin' => $total_admin,
            'all_users' => $all_users, // CRITICAL: Pass user list to the view
        ];

        // 3. Load the dashboard view with the data
        $this->call->view('admin/dashboard', $data);
    }
}
