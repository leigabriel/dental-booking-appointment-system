<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Management extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model(['DoctorModel', 'ServiceModel', 'AppointmentModel', 'UserModel']);
        $this->call->library('Form_validation');
        $this->call->helper(['url', 'language']);
        $this->_check_admin_or_staff();
    }

    private function _check_admin_or_staff()
    {
        $role = $this->session->userdata('role');
        if (!$this->session->userdata('is_logged_in') || !in_array($role, ['admin', 'staff'])) {
            $this->session->set_flashdata('error_message', 'Access denied. Admin or Staff privileges required.');
            redirect('login');
        }
    }

    private function _check_admin()
    {
        $role = $this->session->userdata('role');
        if ($role !== 'admin') {
            $this->session->set_flashdata('error_message', 'Access denied. Admin privileges required.');
            redirect('management/appointments');
        }
    }

    private function _fetchUserDetails()
    {
        $loggedInUserId = $this->session->userdata('user_id');
        if (!$loggedInUserId) {
            $this->session->set_flashdata('error_message', 'Session invalid. Please login again.');
            redirect('login');
        }
        $userDetails = $this->UserModel->find($loggedInUserId);
        if (!$userDetails) {
            $this->session->set_flashdata('error_message', 'User account not found.');
            $this->session->sess_destroy();
            redirect('login');
        }
        return $userDetails;
    }

    public function appointments()
    {
        $userDetails = $this->_fetchUserDetails();
        $data['appointments'] = $this->AppointmentModel->all() ?? [];
        $data['doctors'] = array_column($this->DoctorModel->all() ?? [], null, 'id');
        $data['services'] = array_column($this->ServiceModel->all() ?? [], null, 'id');
        $data['users'] = array_column($this->UserModel->all() ?? [], null, 'id');
        $data['userDetails'] = $userDetails;
        $this->call->view('admin/appointments', $data);
    }

    public function doctors()
    {
        $userDetails = $this->_fetchUserDetails();
        $doctors_list = $this->DoctorModel->all() ?? [];
        $data['doctors_list_json'] = json_encode(array_column($doctors_list, null, 'id'));
        $data['doctors'] = $doctors_list;
        $data['errors'] = $this->session->flashdata('errors');
        $data['post_data'] = $this->session->flashdata('post_data');
        $data['userDetails'] = $userDetails;
        $this->call->view('admin/doctor_management', $data);
    }

    public function services()
    {
        $userDetails = $this->_fetchUserDetails();
        $services_list = $this->ServiceModel->all() ?? [];
        $data['services_list_json'] = json_encode(array_column($services_list, null, 'id'));
        $data['services'] = $services_list;
        $data['errors'] = $this->session->flashdata('errors');
        $data['post_data'] = $this->session->flashdata('post_data');
        $data['userDetails'] = $userDetails;
        $this->call->view('admin/service_management', $data);
    }

    public function appointment_confirm($id)
    {
        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid appointment ID.');
            redirect('management/appointments');
        }
        $this->AppointmentModel->update($id, ['status' => 'confirmed']);
        $this->session->set_flashdata('success_message', "Appointment #{$id} confirmed successfully.");
        redirect('management/appointments');
    }

    public function appointment_cancel($id)
    {
        if (!$id) {
            $this->session->set_flashdata('error_message', 'Invalid appointment ID.');
            redirect('management/appointments');
        }
        $this->AppointmentModel->update($id, ['status' => 'cancelled']);
        $this->session->set_flashdata('success_message', "Appointment #{$id} cancelled successfully.");
        redirect('management/appointments');
    }

    public function doctor_add_update($id = null)
    {
        if ($this->io->method() !== 'POST') {
            redirect('management/doctors');
        }

        $post_data = $this->io->post();
        $is_update = !empty($id);

        $this->form_validation
            ->name('name|Doctor Name')->required()->valid_name()
            ->name('specialty|Specialty')->required()
            ->name('email|Email')->required()->valid_email();

        $existingDoctor = null;
        if ($is_update) {
            $existingDoctor = $this->DoctorModel->filter(['email' => $post_data['email']])->not_where('id', $id)->get();
        } else {
            $existingDoctor = $this->DoctorModel->filter(['email' => $post_data['email']])->get();
        }

        if ($existingDoctor) {
            $this->form_validation->set_error('email', 'Email address is already in use by another doctor.');
        }

        if ($this->form_validation->run()) {
            $save_data = [
                'name' => $post_data['name'],
                'specialty' => $post_data['specialty'],
                'email' => $post_data['email']
            ];

            if ($is_update) {
                $this->DoctorModel->update($id, $save_data);
                $this->session->set_flashdata('success_message', 'Doctor updated successfully.');
            } else {
                $this->DoctorModel->insert($save_data);
                $this->session->set_flashdata('success_message', 'New doctor added successfully.');
            }
        } else {
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            $this->session->set_flashdata('post_data', $post_data);
            $this->session->set_flashdata('error_message', 'Validation failed. Please check the form.');
        }
        redirect('management/doctors');
    }

    public function doctor_delete($id)
    {
        $this->_check_admin();
        $doctor = $this->DoctorModel->find($id);
        if (!$doctor) {
            $this->session->set_flashdata('error_message', 'Doctor not found.');
            redirect('management/doctors');
        }

        $this->AppointmentModel->filter(['doctor_id' => $id])->delete();
        $this->DoctorModel->delete($id);

        $this->session->set_flashdata('success_message', "Doctor '{$doctor['name']}' deleted successfully.");
        redirect('management/doctors');
    }

    public function service_add_update($id = null)
    {
        if ($this->io->method() !== 'POST') {
            redirect('management/services');
        }

        $post_data = $this->io->post();
        $is_update = !empty($id);

        $this->form_validation
            ->name('name|Service Name')->required()
            ->name('price|Price')->required()->numeric()
            ->name('duration_mins|Duration')->required()->numeric();

        if ($this->form_validation->run()) {
            $save_data = [
                'name' => $post_data['name'],
                'price' => $post_data['price'],
                'duration_mins' => $post_data['duration_mins']
            ];

            if ($is_update) {
                $this->ServiceModel->update($id, $save_data);
                $this->session->set_flashdata('success_message', 'Service updated successfully.');
            } else {
                $this->ServiceModel->insert($save_data);
                $this->session->set_flashdata('success_message', 'New service added successfully.');
            }
        } else {
            $this->session->set_flashdata('errors', $this->form_validation->get_errors());
            $this->session->set_flashdata('post_data', $post_data);
            $this->session->set_flashdata('error_message', 'Validation failed. Please check the form.');
        }
        redirect('management/services');
    }

    public function service_delete($id)
    {
        $this->_check_admin();
        $service = $this->ServiceModel->find($id);
        if (!$service) {
            $this->session->set_flashdata('error_message', 'Service not found.');
            redirect('management/services');
        }

        $this->AppointmentModel->filter(['service_id' => $id])->delete();
        $this->ServiceModel->delete($id);

        $this->session->set_flashdata('success_message', "Service '{$service['name']}' deleted successfully.");
        redirect('management/services');
    }
}