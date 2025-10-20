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

    // NEW FUNCTION: Allows Staff for Read/Update access to management pages
    private function _check_management_access()
    {
        $role = $this->session->userdata('role');
        if ($role !== 'admin' && $role !== 'staff') {
            $this->session->set_flashdata('error_message', 'Access denied. Admin or Staff privileges required.');
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

    // --- Action: Confirm Appointment ---
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

    // --- Action: Cancel Appointment ---
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

    // --- 2. Doctor Management CRUD (Admin/Staff for everything but Delete) ---

    public function doctors()
    {
        $this->_check_management_access(); // <--- UPDATED
        // ... (rest of method)
        $data['doctors'] = $this->DoctorModel->all();
        // Pass any lingering flash data (errors or old input)
        $data['errors'] = $this->session->flashdata('errors');
        $data['post_data'] = $this->session->flashdata('post_data');

        $this->call->view('admin/doctor_management', $data);
    }

    public function doctor_edit($id)
    {
        $this->_check_management_access(); // <--- UPDATED
        // ... (rest of method)
        $data['doctor'] = $this->DoctorModel->find($id);

        if (!$data['doctor']) {
            $this->session->set_flashdata('error_message', 'Doctor not found.');
            redirect('management/doctors');
        }

        // Pass existing flash data from failed submission, if present
        $data['errors'] = $this->session->flashdata('errors');
        $data['post_data'] = $this->session->flashdata('post_data');

        // Use a dedicated view template for the edit form.
        $this->call->view('admin/doctor_edit_form', $data);
    }

    public function doctor_add_update($id = null)
    {
        $this->_check_management_access(); // <--- UPDATED

        if ($this->io->method() !== 'POST') {
            redirect('management/doctors'); // Ensure only POST is processed here
        }

        $data = $this->io->post();
        // ... (rest of method)
        $this->form_validation
            ->name('name|Doctor Name')->required()->valid_name()
            ->name('specialty|Specialty')->required()
            ->name('email|Email')->required()->valid_email();

        if ($this->form_validation->run()) {
            $save_data = $this->io->post();

            // Clean data for model
            unset($save_data['lava_csrf_token']);
            unset($save_data[config_item('csrf_token_name')]);

            if ($id) {
                $this->DoctorModel->update($id, $save_data);
                $this->session->set_flashdata('success_message', 'Doctor updated successfully.');
            } else {
                $this->DoctorModel->insert($save_data);
                $this->session->set_flashdata('success_message', 'New doctor added successfully.');
            }
        } else {
            // If validation fails, flash errors and old input, then redirect to the correct form.
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            $this->session->set_flashdata('post_data', $this->io->post());
            $this->session->set_flashdata('error_message', 'Validation failed. Please check the form.');

            $redirect_url = $id ? 'management/doctor_edit/' . $id : 'management/doctors';
            redirect($redirect_url);
        }

        // Redirect to the main listing page after success
        redirect('management/doctors');
    }

    public function doctor_delete($id)
    {
        $this->_check_admin(); // <--- RESTRICTED TO ADMIN ONLY (No Change)

        if (!$this->DoctorModel->find($id)) {
            $this->session->set_flashdata('error_message', 'Doctor not found.');
            redirect('management/doctors');
        }

        // FIX: Explicitly delete appointments linked to this doctor.
        // The filter() method returns the underlying database object ($this->db) with the WHERE clause set.
        $this->AppointmentModel->filter(['doctor_id' => $id])->delete();

        $this->DoctorModel->delete($id);
        $this->session->set_flashdata('success_message', 'Doctor deleted successfully.');
        redirect('management/doctors');
    }

    // --- 3. Service Management CRUD (Admin/Staff for everything but Delete) ---

    public function services()
    {
        $this->_check_management_access(); // <--- UPDATED
        // ... (rest of method)
        $data['services'] = $this->ServiceModel->all();
        // Pass any lingering flash data (errors or old input)
        $data['errors'] = $this->session->flashdata('errors');
        $data['post_data'] = $this->session->flashdata('post_data');

        $this->call->view('admin/service_management', $data);
    }

    public function service_edit($id)
    {
        $this->_check_management_access(); // <--- UPDATED
        // ... (rest of method)
        $data['service'] = $this->ServiceModel->find($id);

        if (!$data['service']) {
            $this->session->set_flashdata('error_message', 'Service not found.');
            redirect('management/services');
        }

        // Pass existing flash data from failed submission, if present
        $data['errors'] = $this->session->flashdata('errors');
        $data['post_data'] = $this->session->flashdata('post_data');

        // Use a dedicated view template for the edit form.
        $this->call->view('admin/service_edit_form', $data);
    }

    public function service_add_update($id = null)
    {
        $this->_check_management_access(); // <--- UPDATED

        if ($this->io->method() !== 'POST') {
            redirect('management/services'); // Ensure only POST is processed here
        }

        $data = $this->io->post();
        // ... (rest of method)
        $this->form_validation
            ->name('name|Service Name')->required()
            ->name('price|Price')->required()->numeric()
            ->name('duration_mins|Duration')->required()->numeric();

        if ($this->form_validation->run()) {
            $save_data = $this->io->post();

            // Clean data for model
            unset($save_data['lava_csrf_token']);
            unset($save_data[config_item('csrf_token_name')]);

            if ($id) {
                $this->ServiceModel->update($id, $save_data);
                $this->session->set_flashdata('success_message', 'Service updated successfully.');
            } else {
                $this->ServiceModel->insert($save_data);
                $this->session->set_flashdata('success_message', 'New service added successfully.');
            }
        } else {
            // If validation fails, flash errors and old input, then redirect to the correct form.
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            $this->session->set_flashdata('post_data', $this->io->post());
            $this->session->set_flashdata('error_message', 'Validation failed. Please check the form.');

            $redirect_url = $id ? 'management/service_edit/' . $id : 'management/services';
            redirect($redirect_url);
        }

        // Redirect to the main listing page after success
        redirect('management/services');
    }

    public function service_delete($id)
    {
        $this->_check_admin(); // <--- RESTRICTED TO ADMIN ONLY (No Change)

        if (!$this->ServiceModel->find($id)) {
            $this->session->set_flashdata('error_message', 'Service not found.');
            redirect('management/services');
        }

        // FIX: Explicitly delete appointments linked to this service.
        // The filter() method returns the underlying database object ($this->db) with the WHERE clause set.
        $this->AppointmentModel->filter(['service_id' => $id])->delete();

        $this->ServiceModel->delete($id);
        $this->session->set_flashdata('success_message', 'Service deleted successfully.');
        redirect('management/services');
    }
}
