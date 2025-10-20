<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Management extends Controller
{
    public function __construct()
    {
        parent::__construct();

        // Authorization check: Only Admin can access these tools
        if ($this->session->userdata('role') !== 'admin') {
            redirect('login');
        }

        // Load all management dependencies
        $this->call->model(['DoctorModel', 'ServiceModel', 'AppointmentModel', 'UserModel']);
        $this->call->library('Form_validation');
        $this->call->database();
        $this->call->helper(['url', 'language']);
    }

    // --- 1. Appointments Overview (Read All) ---

    public function appointments()
    {
        // ... (data fetching remains the same) ...
        $data['appointments'] = $this->AppointmentModel->all();
        $data['doctors'] = array_column($this->DoctorModel->all() ?? [], null, 'id');
        $data['services'] = array_column($this->ServiceModel->all() ?? [], null, 'id');
        $data['users'] = array_column($this->UserModel->all() ?? [], null, 'id');

        $this->call->view('admin/appointments', $data);
    }

    // --- NEW: Confirm Appointment ---
    public function appointment_confirm($id)
    {
        if (!$id) {
            redirect('management/appointments');
        }

        $this->AppointmentModel->update($id, ['status' => 'confirmed']);
        $this->session->set_flashdata('success_message', "Appointment #{$id} confirmed.");
        redirect('management/appointments');
    }

    // --- NEW: Cancel Appointment ---
    public function appointment_cancel($id)
    {
        if (!$id) {
            redirect('management/appointments');
        }

        $this->AppointmentModel->update($id, ['status' => 'cancelled']);
        $this->session->set_flashdata('success_message', "Appointment #{$id} cancelled.");
        redirect('management/appointments');
    }

    // --- 2. Doctor Management CRUD ---

    public function doctors()
    {
        $data['doctors'] = $this->DoctorModel->all();
        $this->call->view('admin/doctor_management', $data);
    }

    public function doctor_add_update($id = null)
    {
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

            // CRITICAL FIX: Remove ALL potential CSRF token keys from the array
            unset($save_data['lava_csrf_token']); // Targets the name shown in the error trace
            unset($save_data[config_item('csrf_token_name')]); // Targets the configured name

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
        $this->DoctorModel->delete($id);
        $this->session->set_flashdata('success_message', 'Doctor deleted successfully.');
        redirect('management/doctors');
    }

    // --- 3. Service Management CRUD ---

    public function services()
    {
        $data['services'] = $this->ServiceModel->all();
        $this->call->view('admin/service_management', $data);
    }

    public function service_add_update($id = null)
    {
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

            // CRITICAL FIX: Remove ALL potential CSRF token keys from the array
            unset($save_data['lava_csrf_token']); // Targets the name shown in the error trace
            unset($save_data[config_item('csrf_token_name')]); // Targets the configured name

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
        $this->ServiceModel->delete($id);
        $this->session->set_flashdata('success_message', 'Service deleted successfully.');
        redirect('management/services');
    }
}
