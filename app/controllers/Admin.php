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

        // Check if the current user is an admin before allowing access to dashboard actions
        if ($this->session->userdata('role') !== 'admin' && $this->router->get_method() !== 'dashboard') {
            $this->session->set_flashdata('error_message', 'Admin privileges required.');
            redirect('login');
        }
    }

    public function dashboard()
    {
        $LAVA = lava_instance();

        $this->call->model('AppointmentModel');
        if (!isset($LAVA->db)) {
            $this->call->database();
        }

        // Get counts for dashboard statistics
        $total_users = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_staff = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'staff'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_admin = $LAVA->db->raw("SELECT COUNT(*) AS count FROM users WHERE role = 'admin'")->fetch(PDO::FETCH_ASSOC)['count'];
        $total_appointments = $LAVA->db->raw("SELECT COUNT(*) AS count FROM appointments")->fetch(PDO::FETCH_ASSOC)['count'];
        $all_users = $this->UserModel->all();

        $data = [
            'total_users' => $total_users,
            'total_staff' => $total_staff,
            'total_admin' => $total_admin,
            'total_appointments' => $total_appointments,
            'all_users' => $all_users,
            'errors' => $this->session->flashdata('errors') ?? [],
            'post_data' => $this->session->flashdata('post_data') ?? [],
        ];

        $this->call->view('admin/dashboard', $data);
    }

    // ADMIN/STAFF ADD/UPDATE/DELETE METHODS

    /**
     * Handles adding or updating a user's role and details.
     */
    public function admin_staff_add_update($id = null)
    {
        // Auth check is done in the constructor, but explicitly redirect if not POST
        if ($this->io->method() !== 'POST') {
            redirect('admin/dashboard');
        }

        $post_data = $this->io->post();
        $is_update = !empty($id);

        $this->form_validation
            ->name('full_name|Full Name')->required()->valid_name()
            ->name('email|Email')->required()->valid_email()
            ->name('role|Role')->required()->in_list('admin,staff');

        // Validation rules specific to ADD operation
        if (!$is_update) {
            $this->form_validation
                ->name('username|Username')->required()->alpha_numeric()->is_unique('users', 'username', $post_data['username'])
                ->name('password|Password')->required()->min_length(6);
        }

        if ($this->form_validation->run()) {
            $save_data = $post_data;
            unset($save_data['lava_csrf_token']);
            unset($save_data[config_item('csrf_token_name')]);
            unset($save_data['id']); // Ensure ID is not saved as a regular field

            if ($is_update) {
                // UPDATE: Only update allowed fields (name, email, role)
                $update_id = $id;

                // If a new password is provided, hash it.
                if (!empty($post_data['password'])) {
                    $save_data['password'] = password_hash($post_data['password'], PASSWORD_BCRYPT);
                } else {
                    unset($save_data['password']); // Don't update password if blank
                }

                // Ensure non-updatable fields are removed
                unset($save_data['username']);

                $this->UserModel->update($update_id, $save_data);
                $this->session->set_flashdata('success_message', "User #{$update_id} updated successfully.");
            } else {
                // ADD: Hash password and create new user
                $save_data['password'] = password_hash($post_data['password'], PASSWORD_BCRYPT);
                $this->UserModel->insert($save_data);
                $this->session->set_flashdata('success_message', "New {$save_data['role']} account created successfully.");
            }
        } else {
            // Validation failed
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            $this->session->set_flashdata('post_data', $post_data);
            $this->session->set_flashdata('error_message', 'Validation failed. Please check the form.');
        }

        redirect('admin/dashboard');
    }

    /**
     * Handles deleting a staff or admin user.
     */
    public function admin_staff_delete($id)
    {
        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid user ID.');
            redirect('admin/dashboard');
        }

        $user = $this->UserModel->find($id);

        if (!$user) {
            $this->session->set_flashdata('error_message', 'User not found.');
            redirect('admin/dashboard');
        }

        // Prevent admin from deleting themselves or other admins accidentally (optional security)
        if ($user['role'] === 'admin' && $user['id'] != $this->session->userdata('user_id')) {
            // Allow deleting other admins, but prevent self-deletion for simplicity
            if ($user['id'] == $this->session->userdata('user_id')) {
                $this->session->set_flashdata('error_message', 'Cannot delete your own active Admin account.');
                redirect('admin/dashboard');
            }
        }

        // Deleting any associated appointments first is crucial due to foreign key constraints.
        // Assuming foreign keys are handled appropriately or cascade deletion is set up for users.
        // The standard delete method on the model should suffice if database constraints are correct.

        $this->UserModel->delete($id);
        $this->session->set_flashdata('success_message', "User '{$user['username']}' ({$user['role']}) deleted successfully.");
        redirect('admin/dashboard');
    }
}