<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Management extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Load all management dependencies
        $this->call->model(['DoctorModel', 'ServiceModel', 'AppointmentModel', 'UserModel']);
        $this->call->library('Form_validation');
        $this->call->database(); // Explicitly ensures DB is available for actions
        $this->call->helper(['url', 'language']);
    }

    // --- UTILITY: Authorization Check ---
    private function _check_admin_or_staff()
    {
        $role = $this->session->userdata('role');
        if ($role !== 'admin' && $role !== 'staff') {
            $this->session->set_flashdata('error_message', 'Access denied.');
            redirect('login');
        }
    }

    private function _check_admin()
    {
        $role = $this->session->userdata('role');
        if ($role !== 'admin') {
            $this->session->set_flashdata('error_message', 'Access denied. Admin privileges required.');
            redirect('login');
        }
    }

    // --- 1. Appointments Overview ---

    public function appointments()
    {
        $this->_check_admin_or_staff(); // Auth check

        $data['appointments'] = $this->AppointmentModel->all();
        $data['doctors'] = array_column($this->DoctorModel->all() ?? [], null, 'id');
        $data['services'] = array_column($this->ServiceModel->all() ?? [], null, 'id');
        $data['users'] = array_column($this->UserModel->all() ?? [], null, 'id');

        $this->call->view('admin/appointments', $data);
    }

    // --- Action: Confirm Appointment (FIXED) ---
    public function appointment_confirm($id)
    {
        $this->_check_admin_or_staff(); // Auth check

        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid appointment ID.');
            redirect('management/appointments');
        }

        $this->AppointmentModel->update($id, ['status' => 'confirmed']);

        $this->session->set_flashdata('success_message', "Appointment #{$id} confirmed successfully.");
        redirect('management/appointments');
    }

    // --- Action: Cancel Appointment (FIXED) ---
    public function appointment_cancel($id)
    {
        $this->_check_admin_or_staff(); // Auth check

        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid appointment ID.');
            redirect('management/appointments');
        }

        $this->AppointmentModel->update($id, ['status' => 'cancelled']);

        $this->session->set_flashdata('success_message', "Appointment #{$id} cancelled successfully.");
        redirect('management/appointments');
    }

    // --- 2. Doctor Management CRUD (Admin Only) ---

    public function doctors()
    {
        $this->_check_admin(); // Auth check

        $data['doctors'] = $this->DoctorModel->all();
        $this->call->view('admin/doctor_management', $data);
    }

    public function doctor_add_update($id = null)
    {
        $this->_check_admin(); // Auth check

        $data = $this->io->post();

        $this->form_validation
            ->name('name|Doctor Name')->required()->valid_name()
            ->name('specialty|Specialty')->required()
            ->name('email|Email')->required()->valid_email();

        if ($id) {
            $data['doctor'] = $this->DoctorModel->find($id);
        }

        if ($this->form_validation->run()) {
            $save_data = $this->io->post();

            // Remove CSRF token keys
            unset($save_data['lava_csrf_token']);
            unset($save_data[config_item('csrf_token_name')]);

            if ($id) {
                $this->DoctorModel->update($id, $save_data);
                $this->session->set_flashdata('success_message', 'Doctor updated successfully.');
            } else {
                $this->DoctorModel->insert($save_data);
                $this->session->set_flashdata('success_message', 'New doctor added successfully.');
            }
            redirect('management/doctors');
        }

        $this->call->view('admin/doctor_management', array_merge($data, ['errors' => $this->form_validation->get_errors()]));
    }

    public function doctor_delete($id)
    {
        $this->_check_admin(); // Auth check

        $this->DoctorModel->delete($id);
        $this->session->set_flashdata('success_message', 'Doctor deleted successfully.');
        redirect('management/doctors');
    }

    // --- 3. Service Management CRUD (Admin Only) ---

    public function services()
    {
        $this->_check_admin(); // Auth check

        $data['services'] = $this->ServiceModel->all();
        $this->call->view('admin/service_management', $data);
    }

    public function service_add_update($id = null)
    {
        $this->_check_admin(); // Auth check

        $data = $this->io->post();

        $this->form_validation
            ->name('name|Service Name')->required()
            ->name('price|Price')->required()->numeric()
            ->name('duration_mins|Duration')->required()->numeric();

        if ($id) {
            $data['service'] = $this->ServiceModel->find($id);
        }

        if ($this->form_validation->run()) {
            $save_data = $this->io->post();

            // Remove CSRF token keys
            unset($save_data['lava_csrf_token']);
            unset($save_data[config_item('csrf_token_name')]);

            if ($id) {
                $this->ServiceModel->update($id, $save_data);
                $this->session->set_flashdata('success_message', 'Service updated successfully.');
            } else {
                $this->ServiceModel->insert($save_data);
                $this->session->set_flashdata('success_message', 'New service added successfully.');
            }
            redirect('management/services');
        }

        $this->call->view('admin/service_management', array_merge($data, ['errors' => $this->form_validation->get_errors()]));
    }

    public function service_delete($id)
    {
        $this->_check_admin(); // Auth check

        $this->ServiceModel->delete($id);
        $this->session->set_flashdata('success_message', 'Service deleted successfully.');
        redirect('management/services');
    }
}
