<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Staff extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UserModel');
        $this->call->database();
        $this->call->helper('url');
        $this->call->helper('language');
    }

    public function dashboard()
    {
        $LAVA = lava_instance();

        $all_users = $this->UserModel->filter(['role' => 'user'])->get_all();

        $data = [
            'all_users' => $all_users,
        ];

        $this->call->view('staff/dashboard', $data);
    }
}